CREATE DATABASE cq2_oxidian;

USE cq2_oxidian;

CREATE TABLE victims
(
	id INT AUTO_INCREMENT,
	curseName TEXT,
	expireDate DATETIME,
	user TEXT,
	numShards INT,
	crit TEXT,
	PRIMARY KEY (id)
);

CREATE TABLE reveals
(
	id INT AUTO_INCREMENT,
	user TEXT NOT NULL,
	list TEXT NOT NULL,
	userLevel TINYINT NOT NULL,
	submissionDate DATETIME NOT NULL,
	client_IP INT NOT NULL,
	PRIMARY KEY (id)	
);

CREATE TABLE visitorStats
(
	id INT AUTO_INCREMENT,
	client_IP INT NOT NULL,
	user_agent TEXT NOT NULL,
	visited_time DATETIME NOT NULL,
	visited_page TEXT NOT NULL,
	PRIMARY KEY (id)	
);