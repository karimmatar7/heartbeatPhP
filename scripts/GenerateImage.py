import argparse
import json
import logging
import os
from datetime import datetime
from PIL import Image
from io import BytesIO
import requests

# Logging configuration
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler("image_generation.log"),
        logging.StreamHandler()
    ]
)

API_URL = "https://api-inference.huggingface.co/models/mann-e/Mann-E_Turbo"
API_KEY = "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"

headers = {
    "Authorization": f"Bearer {API_KEY}"
}

# Get the script directory and resolve `data.json` path
script_dir = os.path.dirname(os.path.abspath(__file__))
data_path = os.path.join(script_dir, "data.json")

# Load JSON data with error handling
try:
    with open(data_path, "r") as f:
        PROMPT_DATA = json.load(f)
except FileNotFoundError:
    logging.error(f"Error: The file 'data.json' was not found at {data_path}. Please ensure it exists.")
    exit(1)
except json.JSONDecodeError:
    logging.error("Error: Failed to decode 'data.json'. Please ensure it contains valid JSON.")
    exit(1)

def get_prompt_from_json(category, value):
    """
    Retrieves the appropriate prompt based on the value from the JSON data.
    """
    for item in PROMPT_DATA[category]:
        if item["parameters"]["min"] <= value < item["parameters"]["max"]:
            return item["prompt"]
    return "No matching prompt found."

def generate_dynamic_image_prompt(heartrate, bodytemp, time):
    """
    Generates a descriptive prompt for an abstract image based on input values.
    """
    hr_element = get_prompt_from_json("heartrate", heartrate)
    temp_element = get_prompt_from_json("temperature", bodytemp)

    try:
        minutes = int(time.split(":")[1])
    except (ValueError, IndexError):
        minutes = 0

    time_element = get_prompt_from_json("timestamp", minutes)

    prompt = (
        f"An abstract composition featuring {hr_element}, "
        f"enhanced by {temp_element}. "
        f"and is completed with {time_element}. "
        f"The elements blend seamlessly to create a cohesive and evocative image."
    )
    logging.info(f"Generated prompt: {prompt}")
    return prompt

def generate_image(heartrate, bodytemp, time, output_path):
    """
    Generates an image based on sensor data and saves it locally.
    """
    prompt = generate_dynamic_image_prompt(heartrate, bodytemp, time)
    payload = {"inputs": prompt}

    logging.info(f"Generating image with prompt: {prompt}")

    try:
        response = requests.post(API_URL, headers=headers, json=payload)
        response.raise_for_status()  # Raise an exception for HTTP errors
    except requests.exceptions.RequestException as e:
        logging.error(f"Failed to call API: {e}")
        return None

    if response.status_code == 200:
        image_data = response.content
        try:
            image = Image.open(BytesIO(image_data))
            image.save(output_path)
            logging.info(f"Image saved locally at {output_path}")
            return output_path
        except Exception as e:
            logging.error(f"Failed to save image. Error: {e}")
            return None
    else:
        logging.error(f"Failed to generate image. Status code: {response.status_code}")
        logging.error(f"Error: {response.text}")
        return None

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Generate an abstract image from sensor data.")
    parser.add_argument("--heartrate", type=int, required=True, help="Heart rate value")
    parser.add_argument("--bodytemp", type=float, required=True, help="Body temperature")
    parser.add_argument("--time", type=str, required=True, help="Current time (HH:MM:SS)")
    parser.add_argument("--output", type=str, required=True, help="Output path for the generated image")

    args = parser.parse_args()

    # Check if the output directory exists, if not create it
    output_dir = os.path.dirname(args.output)
    if not os.path.exists(output_dir) and output_dir:
        os.makedirs(output_dir)
        logging.info(f"Created output directory: {output_dir}")

    # Generate the image and handle possible failure
    result = generate_image(args.heartrate, args.bodytemp, args.time, args.output)

    if result:
        logging.info(f"Image successfully generated and saved at {result}")
    else:
        logging.error("Image generation failed.")
