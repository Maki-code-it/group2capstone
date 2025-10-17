# get_recommendations.py - NLP matching for employee recommendations
import sys
import json
import mysql.connector
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import spacy
import re

# Load English language model for spaCy
try:
    nlp = spacy.load("en_core_web_sm")
except:
    # If model isn't available, download it
    from spacy.cli import download
    download("en_core_web_sm")
    nlp = spacy.load("en_core_web_sm")

# Database connection
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="resource_management"
)

def extract_skills(text):
    """Extract skills from text using NLP"""
    if not text:
        return []
        
    doc = nlp(text.lower())
    skills = []
    
    # Pattern matching for common skills
    skill_patterns = [
        r'python', r'java', r'sql', r'javascript', r'html', r'css',
        r'machine learning', r'data analysis', r'project management',
        r'communication', r'problem solving', r'teamwork'
    ]
    
    for pattern in skill_patterns:
        if re.search(pattern, text.lower()):
            skills.append(pattern)
    
    # Extract nouns which often represent skills
    for token in doc:
        if token.pos_ == 'NOUN' and len(token.text) > 3:
            skills.append(token.text)
    
    return list(set(skills))

def get_employee_data():
    """Retrieve employee data from database"""
    cursor = db.cursor(dictionary=True)
    cursor.execute("""
        SELECT e.user_id, u.name, e.skills, e.education, c.extracted_text 
        FROM employees e 
        JOIN users u ON e.user_id = u.id 
        LEFT JOIN certificates c ON e.user_id = c.employee_id
    """)
    
    employees = {}
    for row in cursor.fetchall():
        user_id = row['user_id']
        if user_id not in employees:
            employees[user_id] = {
                'id': user_id,
                'name': row['name'],
                'skills': row['skills'],
                'education': row['education'],
                'certificates': []
            }
        
        if row['extracted_text']:
            employees[user_id]['certificates'].append(row['extracted_text'])
    
    return list(employees.values())

def calculate_similarity(required_skills, employee_skills_text):
    """Calculate similarity between required skills and employee skills"""
    # Create TF-IDF vectorizer
    vectorizer = TfidfVectorizer()
    
    # Combine required skills and employee skills for vectorization
    all_skills = [required_skills, employee_skills_text]
    
    # Create TF-IDF matrix
    tfidf_matrix = vectorizer.fit_transform(all_skills)
    
    # Calculate cosine similarity between the first item (required skills) and the second (employee skills)
    similarity = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:2])
    
    return similarity[0][0]

def main():
    # Get required skills from command line argument
    required_skills = sys.argv[1] if len(sys.argv) > 1 else ""
    
    # Get all employees
    employees = get_employee_data()
    
    # Prepare recommendations
    recommendations = []
    
    for employee in employees:
        # Combine all employee information into a single text for matching
        employee_text = f"{employee['skills']} {employee['education']} {' '.join(employee['certificates'])}"
        
        # Extract skills from employee text
        extracted_skills = extract_skills(employee_text)
        employee_skills_text = " ".join(extracted_skills)
        
        # Calculate similarity score
        score = calculate_similarity(required_skills, employee_skills_text)
        
        recommendations.append({
            'id': employee['id'],
            'name': employee['name'],
            'skills': employee['skills'],
            'score': score
        })
    
    # Sort by score (descending)
    recommendations.sort(key=lambda x: x['score'], reverse=True)
    
    # Return top 10 recommendations as JSON
    print(json.dumps(recommendations[:10]))

if __name__ == "__main__":
    main()
    