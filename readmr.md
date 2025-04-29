# Internship Recommender (PHP/Python/MySQL Version)

Basic web application for recommending internships to students based on their profiles.

## Tech Stack

*   **Frontend:** HTML, CSS, JavaScript (Vanilla)
*   **Backend (Web):** PHP
*   **Backend (Recommendation):** Python (Flask)
*   **Database:** MySQL

## Setup

1.  **Database:**
    *   Ensure MySQL server is running.
    *   Create a database (e.g., `internship_db`).
    *   Import the schema: `mysql -u your_user -p internship_db < database/schema.sql`
    *   (Optional but Recommended) Create a dedicated database user with specific privileges for this application.

2.  **Environment Variables:**
    *   Copy `.env.example` to `.env`.
    *   Edit `.env` and fill in your actual database credentials (`DB_USER`, `DB_PASSWORD`). Ensure these match the user created in step 1.

3.  **Python Recommendation Service:**
    *   Navigate to the `backend_python` directory: `cd backend_python`
    *   Create a virtual environment: `python -m venv venv`
    *   Activate the environment:
        *   Windows: `.\venv\Scripts\activate`
        *   macOS/Linux: `source venv/bin/activate`
    *   Install dependencies: `pip install -r requirements.txt`
    *   Run the service: `python recommend_service.py` (Keep this terminal running)

4.  **PHP Web Application:**
    *   Ensure you have a web server (Apache/Nginx) with PHP configured (including PDO MySQL and cURL extensions).
    *   Place the entire project directory (`internship_recommender/`) in your web server's document root (e.g., `htdocs`, `www`).
    *   Make sure the web server has read access to the project files.
    *   Access the application through your browser (e.g., `http://localhost/internship_recommender/login.php`).

5.  **Generate Data:**
    *   Ensure the Python service virtual environment is activated (`source backend_python/venv/bin/activate`).
    *   Run the data generation script (make sure DB credentials in `.env` are correct): `python scripts/generate_data.py`

6.  **Internship Data:**
    *   Manually add companies and internships to the respective database tables or create a scraping script. The application requires data in the `internships` table to provide recommendations.

## Running

1.  Start the Python Flask service (`python backend_python/recommend_service.py`).
2.  Access the PHP application via your web server (e.g., `http://localhost/internship_recommender/`).

## Important Notes

*   **Security:** This is a basic example. Production deployment requires significant security hardening (HTTPS, input validation, rate limiting, session security, environment variable management, etc.). **Change default passwords!**
*   **Error Handling:** Robust error handling and logging should be implemented.
*   **Styling:** CSS is minimal; enhance it for a better user experience.
*   **Profile Features:** Skills, Courses, and Preferences editing in the profile section is not fully implemented in this skeleton.
*   **Performance:** The Python database query for recommendations needs optimization for large datasets. Consider adding more specific database indexes.
