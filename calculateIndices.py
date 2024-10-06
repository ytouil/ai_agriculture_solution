import sys
import os
import json
from datetime import datetime, timedelta
from geopy.geocoders import Nominatim
import pandas as pd
import traceback

# Add the specific paths to sys.path
sys.path.insert(0, '/opt/lampp/htdocs/websiteNasa/src')
sys.path.insert(0, '/opt/lampp/htdocs/websiteNasa/src/GrowthIndicators')

from GrowthIndicators.handle_gdd import get_gdd_cumulative
from GrowthIndicators.handle_growth_potential import get_growth_potential_day

def json_serial(obj):
    """JSON serializer for objects not serializable by default json code"""
    if isinstance(obj, (datetime, pd.Timestamp)):
        return obj.isoformat()
    raise TypeError(f"Type {type(obj)} not serializable")

def get_location_info(lat, lon):
    geolocator = Nominatim(user_agent="my_geocoding_app")
    try:
        location = geolocator.reverse(f"{lat}, {lon}", exactly_one=True)
        if location:
            address = location.raw['address']
            country = address.get('country_code', 'US').upper()
            state = address.get('state', address.get('county', 'Unknown'))
            city = address.get('city', address.get('town', address.get('village', 'Unknown')))
            postcode = address.get('postcode', 'Unknown')
            return {
                'country': country,
                'state': state,
                'city': city,
                'postcode': postcode
            }
        else:
            return None
    except Exception as e:
        print(json.dumps({"error": f"Geolocation service error: {str(e)}"}))
        return None

def calculate_indices(bottom_left_lat, bottom_left_lon, upper_right_lat, upper_right_lon, plantation_type, desired_date):
    try:
        center_lat = (float(bottom_left_lat) + float(upper_right_lat)) / 2
        center_lon = (float(bottom_left_lon) + float(upper_right_lon)) / 2

        location_info = get_location_info(center_lat, center_lon)
        if location_info is None:
            raise ValueError(f"Could not find location information for coordinates: {center_lat}, {center_lon}")

        print(json.dumps({"info": f"Location: {location_info['city']}, {location_info['state']}, {location_info['country']}"}))

        if location_info['postcode'] == 'Unknown':
            print(json.dumps({"warning": "No postal code found. Using coordinates for calculations."}))
            zipcode = f"{center_lat:.2f},{center_lon:.2f}"
        else:
            zipcode = location_info['postcode']

        print(json.dumps({"info": f"Using identifier: {zipcode} for coordinates: {center_lat}, {center_lon}"}))

        plant_type = 'C3' if plantation_type in ['Rice', 'Wheat', 'Turf'] else 'C4'

        # Use current date instead of desired_date
        current_date = datetime.now()
        start_date = current_date - timedelta(days=7)  # Get data for the past week

        print(json.dumps({"debug": f"Calculating GDD from {start_date.strftime('%Y-%m-%d')} to {current_date.strftime('%Y-%m-%d')}"}))
        gdd_data = json.loads(get_gdd_cumulative(zipcode, location_info['country'], start_date.strftime("%Y-%m-%d"), plant_type))
        print(json.dumps({"debug": f"GDD data: {gdd_data}"}))

        print(json.dumps({"debug": f"Calculating growth potential from {start_date.strftime('%Y-%m-%d')} to {current_date.strftime('%Y-%m-%d')}"}))
        growth_potential = get_growth_potential_day(zipcode, location_info['country'], start_date, current_date, plant_type)
        print(json.dumps({"debug": f"Growth potential data: {growth_potential.to_dict(orient='records') if not growth_potential.empty else []}"}))

        results = {
            "gdd_data": gdd_data,
            "growth_potential": growth_potential.to_dict(orient='records') if not growth_potential.empty else []
        }

        print(json.dumps(results, default=json_serial))
    except Exception as e:
        print(json.dumps({"error": str(e), "traceback": traceback.format_exc()}))

if __name__ == "__main__":
    if len(sys.argv) != 7:
        print(json.dumps({"error": f"Incorrect number of arguments. Expected 6 arguments, got {len(sys.argv) - 1}."}))
    else:
        calculate_indices(*sys.argv[1:])