drop database if exists example_database;
create database if not exists example_database;
use example_database;

create table if not exists accounts (
	id int primary key Auto_Increment,
	username varchar(20) not null unique,
	password varchar(20) not null
) Engine = InnoDB, Auto_Increment = 1;

create table if not exists characters (
	id int primary key Auto_Increment,
	name varchar(20) not null unique,
	id_account int,
	constraint fk_character_account foreign key (id_account)
	references accounts(id)
) Engine = InnoDB, Auto_Increment = 1;

insert into accounts (username, password) values ("account_test1","test_password1"), ("account_test2","test_password2");
insert into characters (id_account, name) values (1, "Character Test 1"), (1, "Character Test 2"), (2, "Character Test 3");