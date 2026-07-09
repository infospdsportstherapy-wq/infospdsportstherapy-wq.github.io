/*==========================================================
  DROP TABLES
==========================================================*/

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS users;


/*==========================================================
  USERS TABLE
==========================================================*/

CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    activity VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


/*==========================================================
  REVIEWS TABLE
==========================================================*/

CREATE TABLE reviews (
    review_id SERIAL PRIMARY KEY,

    user_id INTEGER NOT NULL,

    review TEXT NOT NULL,

    rating INTEGER NOT NULL CHECK (rating >= 3 AND rating <= 5),

    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',

    admin_notes TEXT,

    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    reviewed_at TIMESTAMP NULL,

    CONSTRAINT fk_review_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);


/*==========================================================
  INSERT USERS
==========================================================*/

INSERT INTO users (full_name, activity) VALUES
('Matthew Jacobs', 'Hockey Player'),
('Sarah Williams', 'Distance Runner'),
('Jason Muller', 'Competitive Paintball Player'),
('Nicole Adams', 'CrossFit Athlete'),
('Daniel van Wyk', 'Road Cyclist'),
('Bianca Ferreira', 'Gym Enthusiast'),
('Kyle Petersen', 'Rugby Player'),
('Megan Ross', 'Triathlete'),
('Andrew Botha', 'Trail Runner'),
('Emma de Villiers', 'Olympic Weightlifter');


/*==========================================================
  INSERT REVIEWS
==========================================================*/

INSERT INTO reviews (user_id, review, rating, review_date) VALUES

(
1,
'I play league hockey and after a few intense weekends my legs were constantly tight. Brandon came to me, explained everything during the session and I felt a huge difference before my next match. Highly recommended.',
5,
'2024-01-15 10:30:00'
),

(
2,
'As a marathon runner I constantly struggle with tight calves and hamstrings. The treatment was professional and tailored to exactly what I needed. Recovery after long runs has definitely improved.',
5,
'2024-01-18 14:45:00'
),

(
3,
'I compete in speedball paintball and spend entire weekends diving, sprinting and crawling. My shoulders and lower back were constantly sore. After a sports massage I moved much better and recovered much faster.',
4,
'2024-01-22 09:15:00'
),

(
4,
'Excellent mobile service. Brandon arrived on time, assessed my movement first and focused on the areas that actually needed treatment instead of giving a generic massage. Very knowledgeable.',
5,
'2024-01-25 16:20:00'
),

(
5,
'I cycle several hundred kilometres every month and developed tight hips and lower back pain. The treatment provided some relief and improved my mobility somewhat, though I expected more significant results. It was decent but the pricing felt a bit high for the service provided.',
3,
'2024-02-03 11:00:00'
),

(
6,
'I have had sports massages before but this experience felt much more personalised. Everything was explained clearly and the treatment focused on my specific problem areas.',
5,
'2024-02-08 13:30:00'
),

(
7,
'As a rugby player recovery is just as important as training. The deep tissue treatment reduced soreness after matches and helped me recover much quicker during the season.',
5,
'2024-02-12 15:45:00'
),

(
8,
'I work in an office during the week while training for triathlons. The combination left my neck and back incredibly tight. Brandon did address the main issues, though the treatment could have been more tailored to my specific needs. Overall helpful but felt somewhat rushed during the session.',
3,
'2024-02-18 10:15:00'
),

(
9,
'Professional, friendly and extremely convenient. Having treatment at home saved me time and the session focused on improving movement instead of simply relaxing the muscles.',
5,
'2024-02-25 12:45:00'
),

(
10,
'I compete in Olympic weightlifting and often struggle with shoulder and hip mobility. Regular maintenance sessions have noticeably improved my lifting positions and reduced post-training stiffness.',
4,
'2024-03-05 14:00:00'
);


/*==========================================================
  DISPLAY ALL REVIEWS
==========================================================*/

SELECT
    u.user_id,
    u.full_name,
    u.activity,
    r.review_id,
    r.review,
    r.review_date
FROM users u
INNER JOIN reviews r
    ON u.user_id = r.user_id
ORDER BY r.review_date DESC;


/*==========================================================
  RANDOM 3 REVIEWS
==========================================================*/

SELECT
    u.full_name,
    u.activity,
    r.review
FROM users u
INNER JOIN reviews r
    ON u.user_id = r.user_id
ORDER BY RANDOM()
LIMIT 3;