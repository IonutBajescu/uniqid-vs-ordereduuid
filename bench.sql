CREATE DATABASE bench;

USE bench;

CREATE TABLE uniqid_primarykey (
    `__pk` varchar(23) CHARACTER SET latin1 NOT NULL DEFAULT '',
    `email` varchar(255) UNIQUE,
    PRIMARY KEY (`__pk`),
    KEY `email` (`email`)
);

CREATE TABLE uuid_primarykey (
    `__pk` BINARY(16) NOT NULL,
    `email` varchar(255) UNIQUE,
    PRIMARY KEY (`__pk`),
    KEY `email` (`email`)
);
