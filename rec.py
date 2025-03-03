import mysql.connector
import pandas as pd

# Creating connection object

db_name = 'test2'

print("Connecting to sql localhost\n")

mydb = mysql.connector.connect(
    host="localhost",
    user="root",
    password="admin123",
    database=db_name
)

cursor = mydb.cursor()

from sqlalchemy import create_engine

# Create sqlalchemy engine
engine = create_engine("mysql+pymysql://{user}:{pw}@localhost/{db}"
                       .format(user="root",
                               pw="admin123",
                               db=db_name))

# Printing the connection object
conn = engine.connect()

user_wishlist = pd.DataFrame()

select_stmt = "SELECT buyer_id, GROUP_CONCAT(auction_id) FROM wishlist GROUP BY buyer_id"
cursor.execute(select_stmt)
temp_list = cursor.fetchall()

# Convert temp_list to dictionary
wishlist_dict = {}
for row in temp_list:
    buyer_id = row[0]
    auction_ids = set(row[1].split(','))  # Convert the string into a set of strings
    auction_ids = set(int(auction_id) for auction_id in auction_ids)  # Convert each string to an integer
    wishlist_dict[buyer_id] = auction_ids

 

def jaccard_similarity(set1, set2):
    """Calculate Jaccard Similarity between two sets."""
    intersection = len(set1.intersection(set2))
    union = len(set1.union(set2))
    return intersection / union if union else 0

def recommend_items(user_id, user_wishlists, num_recommendations=5):
    """Recommend items for a given user based on wishlist similarity."""
    target_wishlist = user_wishlists[user_id]
    similarities = {}

    # Calculate similarity with every other user
    for other_user_id, wishlist in user_wishlists.items():
        if other_user_id != user_id:
            similarity = jaccard_similarity(target_wishlist, wishlist)
            similarities[other_user_id] = similarity

    # Sort users by similarity
    sorted_similar_users = sorted(similarities, key=similarities.get, reverse=True)

    # Collect recommendations
    recommendations = set()
    for similar_user in sorted_similar_users:
        recommendations.update(user_wishlists[similar_user] - target_wishlist)
        if len(recommendations) >= num_recommendations:
            break

    return list(recommendations)[:num_recommendations]

# Example user wishlists

# Get recommendations for a specific user
recommended_items = recommend_items(300, wishlist_dict, num_recommendations=5)
print(recommended_items)


