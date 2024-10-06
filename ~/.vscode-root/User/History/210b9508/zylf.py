from datetime import datetime
import sys
import os
import json
from geopy.geocoders import Nominatim

# Add the 'src' directory to the system path so Python can find the GrowthIndicators modules
project_root = os.path.dirname(os.path.abspath(__file__))
src_dir = os.path.join(project_root, 'src')
sys.path.append(src_dir)

# Import your modules from the 'GrowthIndicators' folder
from GrowthIndicators.handle_gdd import get_gdd_cumulative
from GrowthIndicators.handle_growth_potential import get_growth_potential_day

def get_zipcode_from_coords(lat, lon):
    geolocator = Nominatim(user_agent="my_geocoding_app")
    location = geolocator.reverse(f"{lat}, {lon}", exactly_one=True)
    
    if location and "postcode" in location.raw["address"]:
        return location.raw["address"]["postcode"]
    else:
        raise ValueError(f"Could not find a postal code for coordinates: {lat}, {lon}")

def calculate_indices(bottom_left_lat, bottom_left_lon, upper_right_lat, upper_right_lon, plantation_type, desired_date):
    try:
        center_lat = (float(bottom_left_lat) + float(upper_right_lat)) / 2
        center_lon = (float(bottom_left_lon) + float(upper_right_lon)) / 2

        zipcode = get_zipcode_from_coords(center_lat, center_lon)
        print(f"Using postal code: {zipcode} for coordinates: {center_lat}, {center_lon}")

        plant_type = 'C3' if plantation_type in ['Rice', 'Wheat', 'Turf'] else 'C4'

        desired_date_obj = datetime.strptime(desired_date, "%Y-%m-%d")

        gdd_data = json.loads(get_gdd_cumulative(zipcode, "US", desired_date_obj.strftime("%Y-%m-%d"), plant_type))

        growth_potential = get_growth_potential_day(zipcode, "US", desired_date_obj, desired_date_obj, plant_type)

        results = {
            "gdd_data": gdd_data,
            "growth_potential": growth_potential.to_dict(orient='records') if not growth_potential.empty else []
        }

        print(json.dumps(results))
    except Exception as e:
        print(json.dumps({"error": str(e)}))

if __name__ == "__main__":
    if len(sys.argv) != 7:
        print(json.dumps({"error": f"Incorrect number of arguments. Expected 6 arguments, got {len(sys.argv) - 1}."}))
    else:
        calculate_indices(*sys.argv[1:])