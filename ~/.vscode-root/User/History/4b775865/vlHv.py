import sys
import os

# Add the parent directory of HandleGrowthIndicators to the Python path
parent_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.insert(0, parent_dir)

# Now import the module using the correct file name
from get_weather_data import get_weather_data

import json
from datetime import datetime, timedelta
import math
import pandas as pd

def calculate_growth_potential(temp, plant_type):
    if plant_type == 'C3':
        t0 = 20
        var = 5.5
    elif plant_type == 'C4':
        t0 = 31
        var = 7
    else:
        raise ValueError("plant_type must be either 'C3' or 'C4'")
    
    return math.exp(-0.5 * ((temp - t0) / var) ** 2)

def get_growth_potential24h(zipcode, country, date, plant_type):
    start_date = datetime.strptime(date, '%Y-%m-%d')
    end_date = start_date
    
    df = get_weather_data(zipcode, country, start_date, end_date)
    
    gp_values = {}
    for _, row in df.iterrows():
        timestamp = row['timestamp'].strftime('%Y-%m-%dT%H:%M:%S%z')
        temp = row['temperature']
        gp = calculate_growth_potential(temp, plant_type)
        gp_values[timestamp] = round(gp, 4)
    
    return json.dumps(gp_values)

def get_growth_potential_day(zipcode, country, start_date, end_date, plant_type):
    df = get_weather_data(zipcode, country, start_date, end_date)

    if df.empty:
        print(f"No weather data available for growth potential calculation between {start_date} and {end_date}.")
        return pd.DataFrame()  # Return empty DataFrame if no data

    # Check if the 'temperature' column exists before applying the function
    if 'temperature' in df.columns:
        df['growth_potential'] = df['temperature'].apply(lambda x: calculate_growth_potential(x, plant_type))
    else:
        print("No temperature data available for growth potential calculation.")
        return pd.DataFrame()  # Return empty DataFrame if no temperature data

    return df

# This part is not necessary unless you want to test the functions directly
# if __name__ == "__main__":
#     # Add any test code here if needed
#     pass