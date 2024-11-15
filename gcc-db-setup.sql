CREATE DATABASE gcc;
USE gcc;

CREATE TABLE teacher(
    teacher_id INT NOT NULL AUTO_INCREMENT,
    teacher_name VARCHAR(50) NOT NULL,
    teacher_phone VARCHAR(10) NOT NULL,
    teacher_email VARCHAR(50) NOT NULL UNIQUE,
    teacher_password VARCHAR(50) NOT NULL,
    PRIMARY KEY(teacher_id)
);

CREATE TABLE classroom(
    classroom_id INT NOT NULL AUTO_INCREMENT,
    classroom_subject VARCHAR(50) NOT NULL,
    classroom_joining_code CHAR(10) NOT NULL UNIQUE,
    c_teacher_id INT,
    PRIMARY KEY(classroom_id),
    FOREIGN KEY(c_teacher_id) REFERENCES teacher(teacher_id) ON UPDATE CASCADE
);

CREATE TABLE assignment(
    assignment_id INT NOT NULL AUTO_INCREMENT,
    assignment_description VARCHAR(1000),
    assignment_due_date DATE,
    PRIMARY KEY(assignment_id)
);

CREATE TABLE student(
    student_id INT NOT NULL AUTO_INCREMENT,
    student_name VARCHAR(50) NOT NULL,
    student_phone VARCHAR(10) NOT NULL,
    student_email VARCHAR(50) NOT NULL UNIQUE,
    student_password VARCHAR(50) NOT NULL,
    PRIMARY KEY(student_id)
);

CREATE TABLE teaches(
    t_teacher_id INT,
    t_classroom_id INT,
    PRIMARY KEY(t_teacher_id, t_classroom_id),
    FOREIGN KEY(t_teacher_id) REFERENCES teacher(teacher_id) ON UPDATE CASCADE,
    FOREIGN KEY(t_classroom_id) REFERENCES classroom(classroom_id) ON UPDATE CASCADE
);

CREATE TABLE posted_in(
    p_classroom_id INT,
    p_assignment_id INT,
    PRIMARY KEY(p_classroom_id, p_assignment_id),
    FOREIGN KEY(p_classroom_id) REFERENCES classroom(classroom_id) ON UPDATE CASCADE,
    FOREIGN KEY(p_assignment_id) REFERENCES assignment(assignment_id) ON UPDATE CASCADE
);

CREATE TABLE has(
    h_classroom_id INT,
    h_student_id INT,
    PRIMARY KEY(h_classroom_id, h_student_id),
    FOREIGN KEY(h_classroom_id) REFERENCES classroom(classroom_id) ON UPDATE CASCADE,
    FOREIGN KEY(h_student_id) REFERENCES student(student_id) ON UPDATE CASCADE
);

CREATE TABLE assignment_files(
    af_assignment_id INT AUTO_INCREMENT NOT NULL,
    assignment_file LONGBLOB NOT NULL,
    PRIMARY KEY(af_assignment_id),
    FOREIGN KEY(af_assignment_id) REFERENCES assignment(assignment_id) ON UPDATE CASCADE ON DELETE CASCADE
);

-- CREATE TABLE submission(
--     s_assignment_id INT,
--     s_student_id INT,
--     submission_id INT,
--     submission_description VARCHAR(1000),
--     PRIMARY KEY(s_assignment_id, s_student_id, submission_id),
--     FOREIGN KEY(s_assignment_id) REFERENCES assignment(assignment_id) ON UPDATE CASCADE,
--     FOREIGN KEY(s_student_id) REFERENCES student(student_id) ON UPDATE CASCADE
-- );

-- -- redundant ?!?!
-- CREATE TABLE submits(
--     ss_student_id INT NOT NULL,
--     ss_assignment_id INT NOT NULL,
--     ss_submission_id INT NOT NULL,
--     submission_date DATE NOT NULL,
--     PRIMARY KEY(ss_assignment_id, ss_student_id, ss_submission_id),
--     FOREIGN KEY(ss_assignment_id) REFERENCES assignment(assignment_id) ON UPDATE CASCADE,
--     FOREIGN KEY(ss_student_id) REFERENCES student(student_id) ON UPDATE CASCADE,
--     FOREIGN KEY(ss_submission_id) REFERENCES submission(submission_id) ON UPDATE CASCADE
-- );

-- CREATE TABLE submission_files(
--     sf_student_id INT NOT NULL,
--     sf_assignment_id INT NOT NULL,
--     sf_submission_id INT NOT NULL,
--     submission_file BLOB NOT NULL,
--     PRIMARY KEY(sf_assignment_id, sf_student_id, sf_submission_id),
--     FOREIGN KEY(sf_assignment_id) REFERENCES assignment(assignment_id) ON UPDATE CASCADE,
--     FOREIGN KEY(sf_student_id) REFERENCES student(student_id) ON UPDATE CASCADE,
--     FOREIGN KEY(sf_submission_id) REFERENCES submission(submission_id) ON UPDATE CASCADE
-- );

CREATE TABLE submission(
    s_assignment_id INT NOT NULL,
    s_student_id INT NOT NULL,
    submission_id INT NOT NULL,
    submission_description VARCHAR(1000),
    submission_date TIMESTAMP NOT NULL,
    submission_file LONGBLOB,  -- Storing the file directly in this table
    PRIMARY KEY(s_assignment_id, s_student_id, submission_id),
    FOREIGN KEY(s_assignment_id) REFERENCES assignment(assignment_id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY(student_id) REFERENCES student(student_id) ON UPDATE CASCADE ON DELETE CASCADE
);

-- TRIGGER
-- -- submission_id in submission, needs to be assigned a default value. it cant be uto incremented, since
CREATE TRIGGER before_insert_submission
BEFORE INSERT ON submission
FOR EACH ROW
SET NEW.submission_id = (SELECT IFNULL(MAX(submission_id), 0) + 1 FROM submission);

-- PROCEDURE
DELIMITER //

CREATE PROCEDURE GetTruncatedAssignments(
    IN classroomId INT,
    IN descriptionLength INT
)
BEGIN
SELECT
    a.assignment_id,
    IF(LENGTH(a.assignment_description) > descriptionLength,
        CONCAT(SUBSTRING(a.assignment_description, 1, descriptionLength), '...'),
        a.assignment_description) AS truncated_description,
        a.assignment_due_date
    FROM
        posted_in p
    JOIN
        assignment a ON p.p_assignment_id = a.assignment_id
    WHERE
        p.p_classroom_id = classroomId
    ORDER BY
        a.assignment_due_date DESC;
END //

DELIMITER ;
-- calling procedure
-- CALL GetTruncatedAssignments(12, 100);

DELIMITER //
CREATE PROCEDURE delete_old_submissions(IN assignment_id INT, IN student_id INT, IN sub_date DATETIME)
BEGIN
    DELETE FROM submission
    WHERE s_assignment_id = assignment_id
      AND s_student_id = student_id
      AND submission_date < sub_date;
END //
DELIMITER ;
-- CALL delete_old_submissions(12, 3, '2024-11-14 10:09:00');

-- FUNCTION
DELIMITER //
CREATE FUNCTION get_student_count_in_classroom(p_classroom_id INT)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE student_count INT;
    
    SELECT COUNT(*) INTO student_count
    FROM has
    WHERE h_classroom_id = p_classroom_id;

    RETURN student_count;
END//

DELIMITER ;
-- calling function
-- SELECT get_student_count_in_classroom(1) AS student_count;



-- sample inserts
-- teacher table
insert into teacher(teacher_name, teacher_phone, teacher_email, teacher_password) values('abc', '9448300000', 'abc@gmail.com', 'abc');
insert into teacher(teacher_name, teacher_phone, teacher_email, teacher_password) values('def', '9999908080', 'def@gmail.com', 'hello');
insert into teacher(teacher_name, teacher_phone, teacher_email, teacher_password) values('ghi', '9999908080', 'ghi@gmail.com', 'hello');

-- student table
INSERT INTO student (student_id, student_name, student_phone, student_email, student_password) 
VALUES 
    (1, 'abd', '6567890121', 'abc@gmail.com', 'abc'),
    (2, 'hello', '9448300000', 'abcdefg@gmail.com', 'hello'),
    (3, 'ABCD', '9448300000', 'abcd@gmail.com', 'abcd');

-- classroom
insert into classroom(classroom_subject, classroom_joining_code, c_teacher_id) values('dbms', 'abcdefghij', 5);

-- has
insert into has values(2, 1);
insert into has values(2, 2);
insert into has values(2, 3);

--teaches
insert into teaches values(6, 2);
insert into teaches values(7, 2);

-- posted_in
insert into assignment(assignment_description, assignment_due_date) values('assignment-1, submit a pdf', '2024-11-15');
insert into assignment(assignment_description, assignment_due_date) values('assignment-2, submit a csv', '2024-11-30');