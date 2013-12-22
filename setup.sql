CREATE DATABASE cq2_oxidian;

USE cq2_oxidian;

CREATE TABLE victims
(
	id INT NOT NULL AUTO_INCREMENT,
	curseName TEXT,
	expireDate DATETIME,
	user TEXT,
	numShards INT,
	crit TEXT,
	PRIMARY KEY (id)
);

CREATE TABLE reveals
(
	id INT NOT NULL AUTO_INCREMENT,
	user TEXT NOT NULL,
	list TEXT NOT NULL,
	userLevel TINYINT NOT NULL,
	submissionDate DATETIME NOT NULL,
	client_IP BIGINT NOT NULL,
	PRIMARY KEY (id)	
);

CREATE TABLE shards
(
	id INT NOT NULL AUTO_INCREMENT,
	shard TEXT NOT NULL,
	user TEXT NOT NULL,
	amount INT NOT NULL,
	submissionDate DATETIME NOT NULL,
	PRIMARY KEY (id)
);

CREATE TABLE visitorStats
(
	id INT NOT NULL AUTO_INCREMENT,
	client_IP BIGINT NOT NULL,
	user_agent TEXT NOT NULL,
	visited_time DATETIME NOT NULL,
	visited_page TEXT NOT NULL,
	PRIMARY KEY (id)	
);

CREATE TABLE users 
(
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(64) NOT NULL,
    salt VARCHAR(64) NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE searches
(
	id INT NOT NULL AUTO_INCREMENT,
	search TEXT NOT NULL,
	client_IP BIGINT NOT NULL,
	submissionDate DATETIME NOT NULL,
	visited_page TEXT NOT NULL,
	PRIMARY KEY(id)
);