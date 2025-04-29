import csv
import random
# Make sure to install Faker and mysql-connector-python:
# pip install Faker mysql-connector-python bcrypt
from faker import Faker
from datetime import datetime
import mysql.connector
import bcrypt
import os

# --- Database Configuration (Use Environment Variables!) ---
DB_HOST = os.environ.get('DB_HOST', '127.0.0.1')
DB_USER = os.environ.get('DB_USER', 'root') # Change default user
DB_PASS = os.environ.get('DB_PASSWORD', '') # Change default password
DB_NAME = os.environ.get('DB_NAME', 'internship_db')

# --- Generation Configuration ---
NUM_STUDENTS = 100 # Keep low for testing
MAJORS = ['Computer Science', 'Electrical Engineering', 'Mechanical Engineering', 'Business Administration', 'Economics', 'Data Science', 'Physics', 'Biology', 'Marketing']
ACADEMIC_YEARS = ['Freshman', 'Sophomore', 'Junior', 'Senior', 'Graduate']
# --- Make sure these skills/courses exist in your DB (see schema.sql comments) ---
SKILLS_POOL = ['Python', 'Java', 'C++', 'JavaScript', 'React', 'Node.js', 'SQL', 'NoSQL', 'AWS', 'Azure', 'Docker', 'Kubernetes', 'Machine Learning', 'Data Analysis', 'Project Management', 'Communication', 'Teamwork', 'Git', 'Agile', 'Excel', 'CAD', 'Circuit Design', 'Marketing Analytics', 'SEO']
COURSES_POOL = {
    'CS': ['CS101 Intro to Programming', 'CS201 Data Structures'], # Add more codes used in schema
    'EE': ['EE101 Circuit Theory'],
    'BA': ['BA101 Intro to Business'],
    # Add more courses based on schema/actual data
}
INDUSTRIES = ['Technology', 'Finance', 'Consulting', 'Healthcare', 'Engineering', 'Marketing', 'Education', 'Research']
LOCATIONS = ['New York, NY', 'San Francisco, CA', 'Seattle, WA', 'Austin, TX', 'Chicago, IL', 'Boston, MA', 'Remote', 'Los Angeles, CA']

fake = Faker()

# --- Database Connection ---
try:
    conn = mysql.connector.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME
    )
    cursor = conn.cursor(dictionary=True)
    print("Successfully connected to database.")
except mysql.connector.Error as err:
    print(f"Error connecting to database: {err}")
    exit(1)

# --- Helper Functions ---
def get_db_id(table, column, value):
    """Fetch ID from DB, insert if not exists"""
    try:
        cursor.execute(f"SELECT {column}_id FROM {table} WHERE {column}_name = %s", (value,))
        result = cursor.fetchone()
        if result:
            return result[f'{column}_id']
        else:
            # Insert if not found (adjust column names if needed)
            cursor.execute(f"INSERT INTO {table} ({column}_name) VALUES (%s)", (value,))
            conn.commit()
            return cursor.lastrowid
    except mysql.connector.Error as err:
         print(f"DB Error getting/inserting {table} '{value}': {err}")
         # Handle appropriately, maybe skip this entry
         return None

def get_course_id_by_name(course_name_full):
    """ Fetch course ID by full name (assuming name is unique enough here) """
    # Simple split, might need adjustment based on actual course names
    parts = course_name_full.split(' ', 1)
    code = parts[0] if len(parts) > 0 else course_name_full
    name = parts[1] if len(parts) > 1 else ''
    try:
        cursor.execute("SELECT course_id FROM courses WHERE course_code = %s", (code,))
        result = cursor.fetchone()
        if result:
            return result['course_id']
        else:
            cursor.execute("INSERT INTO courses (course_code, course_name) VALUES (%s, %s)", (code, name))
            conn.commit()
            return cursor.lastrowid
    except mysql.connector.Error as err:
         print(f"DB Error getting/inserting course '{code}': {err}")
         return None


def get_random_subset(pool, min_count, max_count):
    count = random.randint(min_count, max_count)
    return random.sample(pool, min(count, len(pool)))

def generate_gpa():
    return round(max(2.0, min(4.0, random.normalvariate(3.3, 0.4))), 2)

def get_relevant_courses(major, year):
    major_prefix = major.split(' ')[0][:2].upper()
    relevant_courses = COURSES_POOL.get(major_prefix, [])
    if not relevant_courses: return []
    year_map = {'Freshman': 1, 'Sophomore': 2, 'Junior': 3, 'Senior': 4, 'Graduate': 5}
    current_year_num = year_map.get(year, 1)
    # Simplified logic for course selection
    num_courses_to_take = current_year_num + random.randint(0, 2)
    return random.sample(relevant_courses, min(num_courses_to_take, len(relevant_courses)))

# --- Main Generation ---
print("Generating student data...")

# Fetch existing skill and course IDs from DB into maps
skill_map = {row['skill_name']: row['skill_id'] for row in cursor.execute("SELECT skill_id, skill_name FROM skills", multi=True)[0].fetchall()}
course_map = {row['course_code']: row['course_id'] for row in cursor.execute("SELECT course_id, course_code FROM courses", multi=True)[0].fetchall()}
print(f"Loaded {len(skill_map)} skills and {len(course_map)} courses from DB.")


for i in range(NUM_STUDENTS):
    user_email = fake.unique.email()
    # IMPORTANT: Use a strong, unique password generation strategy in reality
    password = b"password123" # Placeholder! Hash this
    hashed_password = bcrypt.hashpw(password, bcrypt.gensalt()).decode('utf-8')

    first_name = fake.first_name()
    last_name = fake.last_name()
    major = random.choice(MAJORS)
    year = random.choice(ACADEMIC_YEARS)
    gpa = generate_gpa()

    user_id = None
    student_id = None

    try:
        # Insert into users table
        sql_user = "INSERT INTO users (email, password_hash, role) VALUES (%s, %s, %s)"
        cursor.execute(sql_user, (user_email, hashed_password, 'student'))
        user_id = cursor.lastrowid
        conn.commit()

        # Insert into students table
        sql_student = """
        INSERT INTO students (student_id, first_name, last_name, major, academic_year, gpa, profile_summary, linkedin_url, github_url)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        student_data = (
            user_id, first_name, last_name, major, year, gpa,
            fake.paragraph(nb_sentences=3),
            f'https://linkedin.com/in/{first_name.lower()}{last_name.lower()}{i}',
            f'https://github.com/{first_name.lower()}{i}' if 'Computer Science' in major or 'Data Science' in major else None
        )
        cursor.execute(sql_student, student_data)
        student_id = user_id # Since student_id is FK to user_id
        conn.commit()

        # --- Assign Skills ---
        num_skills = random.randint(4, 10)
        assigned_skills = get_random_subset(SKILLS_POOL, num_skills, num_skills)
        skill_insert_sql = "INSERT IGNORE INTO student_skills (student_id, skill_id) VALUES (%s, %s)"
        skill_data_to_insert = []
        for skill_name in assigned_skills:
            skill_id = skill_map.get(skill_name) # Use pre-fetched map
            if not skill_id: # If skill wasn't pre-loaded, try to get/insert it
                skill_id = get_db_id('skills', 'skill', skill_name)
                if skill_id: skill_map[skill_name] = skill_id # Update map
            if student_id and skill_id:
                 skill_data_to_insert.append((student_id, skill_id))
        if skill_data_to_insert:
            cursor.executemany(skill_insert_sql, skill_data_to_insert)
            conn.commit()

        # --- Assign Courses ---
        assigned_courses_names = get_relevant_courses(major, year)
        course_insert_sql = "INSERT IGNORE INTO student_courses (student_id, course_id) VALUES (%s, %s)"
        course_data_to_insert = []
        for course_name_full in assigned_courses_names:
             course_code = course_name_full.split(' ', 1)[0] # Extract code
             course_id = course_map.get(course_code) # Use pre-fetched map
             if not course_id: # If course wasn't pre-loaded
                 course_id = get_course_id_by_name(course_name_full)
                 if course_id: course_map[course_code] = course_id # Update map
             if student_id and course_id:
                 course_data_to_insert.append((student_id, course_id))
        if course_data_to_insert:
            cursor.executemany(course_insert_sql, course_data_to_insert)
            conn.commit()


        # --- Assign Preferences ---
        pref_insert_sql = "INSERT INTO student_preferences (student_id, preference_type, preference_value, priority) VALUES (%s, %s, %s, %s)"
        pref_data_to_insert = []
        # Industry Prefs
        num_industries = random.randint(1, 2)
        pref_industries = get_random_subset(INDUSTRIES, num_industries, num_industries)
        for idx, industry in enumerate(pref_industries):
            if student_id: pref_data_to_insert.append((student_id, 'Industry', industry, idx + 1))
        # Location Prefs
        num_locations = random.randint(1, 2)
        pref_locations = get_random_subset(LOCATIONS, num_locations, num_locations)
        for idx, location in enumerate(pref_locations):
             if student_id: pref_data_to_insert.append((student_id, 'Location', location, idx + 1))
        if pref_data_to_insert:
            cursor.executemany(pref_insert_sql, pref_data_to_insert)
            conn.commit()

        if (i + 1) % 20 == 0:
            print(f"Generated data for {i + 1}/{NUM_STUDENTS} students...")

    except mysql.connector.Error as err:
        print(f"\nError processing student {i+1} ({user_email}): {err}")
        conn.rollback() # Rollback transaction on error for this student
    except Exception as e:
        print(f"\nUnexpected error processing student {i+1} ({user_email}): {e}")
        conn.rollback()

# --- Cleanup ---
if conn.is_connected():
    cursor.close()
    conn.close()
    print("\nDatabase connection closed.")

print(f"Successfully generated data for approximately {NUM_STUDENTS} students (check logs for errors).")
