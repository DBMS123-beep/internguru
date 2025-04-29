# --- Paste the Python Flask code from the previous response here ---
# Make sure it uses environment variables for DB config as shown
# and includes the calculate_recommendations logic.
from flask import Flask, request, jsonify
import mysql.connector # Or import SQLAlchemy components
import os # For environment variables
# ... rest of the Flask app code ...

app = Flask(__name__)

# --- Configuration ---
# Load from environment variables for security in production
DB_HOST = os.environ.get('DB_HOST', '127.0.0.1')
DB_USER = os.environ.get('DB_USER', 'root') # CHANGE!
DB_PASS = os.environ.get('DB_PASSWORD', '') # CHANGE!
DB_NAME = os.environ.get('DB_NAME', 'internship_db')

def get_db_connection():
    # ... (connection logic) ...
    try:
        conn = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASS,
            database=DB_NAME
        )
        return conn
    except mysql.connector.Error as err:
        print(f"Error connecting to database: {err}")
        # Use proper logging
        return None

def calculate_recommendations(student_id):
    # ... (the core recommendation logic fetching from DB and scoring) ...
    # This needs to be fully implemented as shown before
    conn = get_db_connection()
    if not conn: return []
    cursor = conn.cursor(dictionary=True)
    scored_internships = []
    try:
        # Fetch student data (skills, courses, prefs, profile)
        cursor.execute("SELECT skill_id FROM student_skills WHERE student_id = %s", (student_id,))
        student_skill_ids = {row['skill_id'] for row in cursor.fetchall()}
        # Fetch courses, prefs, gpa, major, year...

        cursor.execute("SELECT gpa, major, academic_year FROM students WHERE student_id = %s", (student_id,))
        student_profile = cursor.fetchone()
        if not student_profile: return [] # Student not found

        # Fetch preferences...

        # Fetch active internships with required/preferred skills/courses (Optimize this query!)
        cursor.execute("""
            SELECT i.internship_id, i.title, i.required_gpa, i.required_major, i.required_year, i.industry, i.location,
                   GROUP_CONCAT(DISTINCT irsk.skill_id) as req_skill_ids,
                   GROUP_CONCAT(DISTINCT ipsk.skill_id) as pref_skill_ids,
                   GROUP_CONCAT(DISTINCT irco.course_id) as req_course_ids,
                   GROUP_CONCAT(DISTINCT ipco.course_id) as pref_course_ids
            FROM internships i
            LEFT JOIN internship_required_skills irsk ON i.internship_id = irsk.internship_id
            LEFT JOIN internship_preferred_skills ipsk ON i.internship_id = ipsk.internship_id
            LEFT JOIN internship_required_courses irco ON i.internship_id = irco.internship_id
            LEFT JOIN internship_preferred_courses ipco ON i.internship_id = ipco.internship_id
            WHERE i.application_deadline >= CURDATE() OR i.application_deadline IS NULL
            GROUP BY i.internship_id
        """) # GROUP_CONCAT returns comma-separated strings, need splitting
        all_internships_raw = cursor.fetchall()

        # --- Scoring Logic ---
        W_SKILL_REQ = 30; W_SKILL_PREF = 15 # Define all weights
        W_COURSE_REQ = 20; W_COURSE_PREF = 10
        W_PREF_INDUSTRY = 15; W_PREF_LOCATION = 10

        for internship in all_internships_raw:
            total_score = 0.0

            # Hard Filters (GPA, Major, Year)
            if internship['required_gpa'] and student_profile.get('gpa', 0) < internship['required_gpa']: continue
            # Add year/major checks...

            # --- Scoring ---
            req_skill_ids = set(map(int, internship['req_skill_ids'].split(','))) if internship['req_skill_ids'] else set()
            pref_skill_ids = set(map(int, internship['pref_skill_ids'].split(','))) if internship['pref_skill_ids'] else set()
            # Calculate skill score
            matched_req_skills = len(student_skill_ids.intersection(req_skill_ids))
            matched_pref_skills = len(student_skill_ids.intersection(pref_skill_ids))
            total_score += (W_SKILL_REQ * matched_req_skills) + (W_SKILL_PREF * matched_pref_skills)

            # Calculate course score (fetch student courses first!)
            # Calculate preference score (fetch student prefs first!)

            if total_score > 0:
                scored_internships.append({
                    'internship_id': internship['internship_id'],
                    'score': total_score
                })

    except mysql.connector.Error as err:
         print(f"Calculate recommendations DB error: {err}")
         return [] # Return empty on error
    finally:
        if conn and conn.is_connected():
            cursor.close()
            conn.close()

    # Rank and return top N
    scored_internships.sort(key=lambda x: x['score'], reverse=True)
    return scored_internships[:20] # Limit results


@app.route('/recommend', methods=['POST'])
def recommend_endpoint():
    # ... (endpoint logic as before) ...
    if not request.is_json: return jsonify({"error": "Request must be JSON"}), 400
    data = request.get_json()
    student_id = data.get('student_id')
    if not student_id: return jsonify({"error": "Missing student_id"}), 400
    try: student_id = int(student_id)
    except ValueError: return jsonify({"error": "Invalid student_id format"}), 400

    results = calculate_recommendations(student_id)
    return jsonify({"recommendations": results})

if __name__ == '__main__':
    # Use host='0.0.0.0' to be accessible externally (e.g., by PHP)
    app.run(debug=False, host='0.0.0.0', port=5001) # Turn Debug OFF for production
