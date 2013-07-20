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