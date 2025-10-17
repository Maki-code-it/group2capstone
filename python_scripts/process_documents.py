# process_documents.py - OCR processing for uploaded documents
import sys
import pytesseract
from PIL import Image
import PyPDF2
import mysql.connector
import os

# Database connection
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="resource_management"
)

def extract_text_from_pdf(pdf_path):
    """Extract text from PDF file"""
    text = ""
    try:
        with open(pdf_path, 'rb') as file:
            reader = PyPDF2.PdfReader(file)
            for page in reader.pages:
                text += page.extract_text() + "\n"
    except Exception as e:
        print(f"Error extracting text from PDF: {e}")
    return text

def extract_text_from_image(image_path):
    """Extract text from image using Tesseract OCR"""
    try:
        img = Image.open(image_path)
        text = pytesseract.image_to_string(img)
        return text
    except Exception as e:
        print(f"Error extracting text from image: {e}")
        return ""

def main():
    if len(sys.argv) < 3:
        print("Usage: python process_documents.py <employee_id> <file_path>")
        return
    
    employee_id = sys.argv[1]
    file_path = sys.argv[2]
    
    # Determine file type and extract text accordingly
    if file_path.lower().endswith(('.png', '.jpg', '.jpeg', '.tiff', '.bmp')):
        extracted_text = extract_text_from_image(file_path)
    elif file_path.lower().endswith('.pdf'):
        extracted_text = extract_text_from_pdf(file_path)
    else:
        print(f"Unsupported file format: {file_path}")
        return
    
    # Store extracted text in database
    cursor = db.cursor()
    sql = "INSERT INTO certificates (employee_id, file_path, extracted_text) VALUES (%s, %s, %s)"
    values = (employee_id, file_path, extracted_text)
    cursor.execute(sql, values)
    db.commit()
    cursor.close()
    
    print(f"Successfully processed document: {file_path}")

if __name__ == "__main__":
    main()