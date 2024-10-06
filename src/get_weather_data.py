from noaa_sdk import NOAA
import pandas as pd
from datetime import datetime, timedelta

noaa = NOAA()

def flatten_dict(d, parent_key='', sep='_'):
    items = []
    for k, v in d.items():
        new_key = f"{parent_key}{sep}{k}" if parent_key else k
        if isinstance(v, dict) and 'value' in v:
            items.append((new_key, v['value']))
        elif isinstance(v, dict):
            items.extend(flatten_dict(v, new_key, sep=sep).items())
        else:
            items.append((new_key, v))
    return dict(items)

def get_weather_data(zipcode, country, start_date, end_date):
    # Get observations from NOAA API
    res = noaa.get_observations(zipcode, country, start=start_date.strftime('%Y-%m-%d'), end=end_date.strftime('%Y-%m-%d'))

    # Convert the generator object to a list to extract the data
    res_list = list(res)

    # If no data is returned, print a message and return an empty DataFrame
    if not res_list:
        print(f"No weather data found for {zipcode} between {start_date} and {end_date}.")
        return pd.DataFrame()  # Return an empty DataFrame if no data is found

    # Debug: Print raw NOAA response to understand structure
    print("Raw NOAA data (as list):", res_list)

    data = []
    for observation in res_list:
        flattened = flatten_dict(observation)

        # Debug: Check if the 'timestamp' field exists
        if 'timestamp' not in flattened:
            print("No 'timestamp' field found, observation:", flattened)

        data.append(flattened)

    df = pd.DataFrame(data)

    # Check if 'timestamp' exists
    if 'timestamp' in df.columns:
        df['timestamp'] = pd.to_datetime(df['timestamp'])
    else:
        # If there's no 'timestamp' field, use 'date' or another available time field
        if 'date' in df.columns:
            df['timestamp'] = pd.to_datetime(df['date'])  # Adjust based on the actual field name
        else:
            raise KeyError("No 'timestamp' or 'date' field found in NOAA data.")

    # Sort by timestamp
    df = df.sort_values('timestamp')

    return df
