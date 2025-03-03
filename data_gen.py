# Importing module 
# mysql -h localhost -u root -padmin123
import mysql.connector
import random
from faker import Faker
import pandas as pd
from datetime import date, timedelta, datetime
import tqdm
import string
import dataloader

# Creating connection object

db_name = 'test2'

print("Connecting to sql localhost\n")

mydb = mysql.connector.connect(
	host = "localhost",
	user = "root",
	password = "admin123",
    database = db_name
)

cursor = mydb.cursor()

from sqlalchemy import create_engine

# Create sqlalchemy engine
engine = create_engine("mysql+pymysql://{user}:{pw}@localhost/{db}"
                       .format(user="root",
                               pw="admin123",
                               db=db_name))

# Printing the connection object 
conn= engine.connect()

# Clear data in wishlist table for dummy data insertion
cursor.execute("DELETE FROM wishlist")
cursor.execute("ALTER TABLE wishlist AUTO_INCREMENT = 1")
mydb.commit()
print("Deleted existing wishlist data\n")

# Clear data in Bid table for dummy data insertion
cursor.execute("DELETE FROM bid")
cursor.execute("ALTER TABLE bid AUTO_INCREMENT = 1")
mydb.commit()
print("Deleted existing bid data\n")


# Clear data from auction table for dummy data insertion
cursor.execute("DELETE FROM auction")
cursor.execute("ALTER TABLE auction AUTO_INCREMENT = 1")
mydb.commit()
print("Deleted existing auction data\n")


# Clear data in User table for dummy data insertion
cursor.execute("DELETE FROM user")
cursor.execute("ALTER TABLE user AUTO_INCREMENT = 1")
mydb.commit()
print("Deleted existing user data\n")

# Generate User data--------------------------------
faker = Faker()
user = pd.DataFrame()
auction = pd.DataFrame()
bid = pd.DataFrame()
item_names = dataloader.get_item_names()

def gen_status (x):
    result = []
    for i in range(x):
        v = random.random()
        if(v>0.5):
            result.append("Buyer")
        else:
            result.append("Seller")
    return result

def gen_passwords(x):

    return [''.join(random.choices(string.ascii_uppercase + string.digits, k=60)) for _ in range (x)]

passwords = gen_passwords(300)
usernames = [faker.unique.name() for i in range(300)]
emails = [faker.unique.email() for i in range(300)]
stats = gen_status(300)

user['username'] = usernames
user['email'] = emails
user['status'] = stats
user['password'] = passwords
#user['user_id'] = user.index

#conn.execute(dele)
#insert data into user table
# Create a new user
test_user = {
    'username': 'John Doe',
    'email': 'johndoe@example.com',
    'status': 'Buyer',
    'password': '$2y$10$9D0g/jfYrlgUWXF7RIfj2O5EuHyave4XezUaWx2akGmJG/mLAl8Iu'
}

# Append the new user to the DataFrame
user = pd.concat([user, pd.DataFrame(test_user, index=[0])], ignore_index=True)

test_user2 = {
    'username': 'Mary Jane',
    'email': 'maryjane@example.com',
    'status': 'Seller',
    'password': '$2y$10$9D0g/jfYrlgUWXF7RIfj2O5EuHyave4XezUaWx2akGmJG/mLAl8Iu'
}

# Append the new user to the DataFrame
user = pd.concat([user, pd.DataFrame(test_user2, index=[0])], ignore_index=True)

# Insert the data into the SQL database
user.to_sql('user', engine, if_exists='append', index=False)
print("New user data has been inserted\n")

# Get a list of user ID to insert as foreign keys
select_stmt = "SELECT user_id FROM user"
cursor.execute(select_stmt)
temp_list = cursor.fetchall()

user_id_list = []
for i in temp_list:
    user_id_list.append(i[0])

user.to_csv("data/user_data", sep='\t')
# End of user generation ---------------------------------------

item_categories = [
    "Paintings",
    "Sculptures",
    "Vintage Watches & Clocks",
    "Collectible Coins & Currency",
    "Necklaces & Pendants",
    "Rings",
    "Vintage Toys",
    "Sports Memorabilia",
    "Comic Books",
    "Computers & Laptops",
    "Cameras & Photography",
    "Mobile Phones & Accessories",
    "Women's Clothing",
    "Handbags & Accessories",
    "Furniture",
    "Home Decor",
    "Classic Cars",
    "Motorcycles",
    "First Edition Books",
    "Guitars"
]


# Generate lists of dummy data for auction table
name_list = random.choices(item_names, k=300)
category_list = random.choices(item_categories, k=300)
seller_id_list = random.choices(user_id_list, k=300)
description_list = paragraphs = [faker.paragraph(nb_sentences=5) for _ in range(300)]
starting_price_list = [round(random.uniform(0, 100), 2) for _ in range(300)]
reserved_price_list = [round(random.uniform(101, 500), 2) for _ in range(300)]

# Generate random dates in set range
def random_date(start, end):
    """Generate a random date between two dates."""
    return start + timedelta(
        days=random.randint(0, (end - start).days)
    )

def random_datetime(start, end):
    """Generate a random datetime between two datetimes."""
    return start + timedelta(
        seconds=random.randint(0, int((end - start).total_seconds()))
    )

end_dates_list = [random_date(date(2022, 1, 1), date(2023, 1, 1)) for _ in range(300)]

end_datetime_list = [random_datetime(datetime(2024, 1, 1, 0, 0)
, datetime(2025, 1, 1, 23, 59, 59)) for _ in range(300)]

auction['Seller_ID'] = seller_id_list
auction['ItemDescription'] = description_list
auction['StartingPrice'] = starting_price_list
auction['ReservedPrice'] = reserved_price_list
auction['Category'] = category_list
auction['EndDate'] = end_datetime_list
auction["itemname"] = name_list
# Generate expired auctions
expired_auctions = auction.sample(n=5)
expired_auctions['EndDate'] = [random_datetime(datetime(2020, 1, 1, 0, 0), datetime(2021, 1, 1, 23, 59, 59)) for _ in range(5)]

# Insert expired auctions into the database
expired_auctions.to_sql('auction', engine, if_exists='append', index=False)
print("Expired auction data has been inserted\n")
mydb.commit()

expired_auctions.to_csv("data/expired_auction_data", sep='\t')

# Insert data into auction table
auction.to_sql('auction', engine, if_exists='append', index=False)
print("New auction data has been inserted\n")
mydb.commit()

auction.to_csv("data/auction_data", sep='\t')


# Dummy bid data generation--------------------------


# Get a list of auction ID to insert as foreign keys
cursor.execute("SELECT Auction_ID FROM auction")
auction_id_list = cursor.fetchall()

bid_price_list = [round(random.uniform(0, 100), 2) for _ in range(300)]

def random_datetime(start, end):
    """Generate a random datetime between two datetimes."""
    return start + timedelta(
        seconds=random.randint(0, int((end - start).total_seconds()))
    )

# Randomly generate a list of K lengthed datetimes
bid_time_list = [random_datetime(datetime(2020, 1, 1, 0, 0), datetime(2021, 12, 29, 23, 59, 59)) for _ in range(300)]

bid['Buyer_ID'] = random.choices(seller_id_list, k=1000)
bid['Auction_ID'] = random.choices(auction_id_list, k=1000)
bid['Amount'] = random.choices(bid_price_list, k=1000)
bid['TimeStamp'] = random.choices(bid_time_list, k=1000)

# Get the ID of the "johndoe" user
cursor.execute("SELECT User_ID FROM user WHERE Username = 'John Doe'")
johndoe_row = cursor.fetchone()
if johndoe_row is not None:
    johndoe_id = johndoe_row[0]
else:
    print("Error: 'johndoe' user not found in database")
    exit(1)

# Get a list of auction IDs to bid on
cursor.execute("SELECT Auction_ID FROM auction")
auction_id_list = cursor.fetchall()

# Generate 10 random bids for johndoe
johndoe_bids = []
for i in range(10):
    auction_id = random.choice(auction_id_list)[0]
    bid_price = round(random.uniform(0, 100), 2)
    bid_time = datetime.now()
    johndoe_bids.append((johndoe_id, auction_id, bid_price, bid_time))

# Insert the bids into the "bid" table
cursor.executemany("INSERT INTO bid (Buyer_ID, Auction_ID, Amount, TimeStamp) VALUES (%s, %s, %s, %s)", johndoe_bids)
mydb.commit()

print("New bids from johndoe have been inserted\n")

# Insert data into bid table
bid.to_sql('bid', engine, if_exists='append', index=False)
print("New bid data has been inserted\n")

bid.to_csv("data/bid_data", sep='\t')
print("Saved bid data to csv.")

# Randomly generate a list of 200 entries for each user_id and auction_id
# Use generated data to seed wishlist sql table
# Warning! These data has no learning value and collabrative filtering should not be applied to randomly generated dataset.

wish_user_id = random.choices(user_id_list, k=3000)
wish_auction_id = random.choices(auction_id_list, k=3000)

wishlist = pd.DataFrame()
wishlist['Buyer_ID'] = wish_user_id
wishlist['Auction_ID'] = wish_auction_id

# Set a composite primary key on the Buyer_ID and Auction_ID columns
wishlist_pk = ['Buyer_ID', 'Auction_ID']
wishlist = wishlist.drop_duplicates(subset=wishlist_pk)
wishlist.to_sql('wishlist', engine, if_exists='append', index=False)
wishlist.to_csv("data/wish_data", sep='\t')

cursor.execute("INSERT INTO bid (buyer_id, auction_id, amount, timestamp) VALUES (301, 1, 200.00, '2023-10-15 12:34:56');")

mydb.commit()
engine.dispose()
mydb.close()