
CREATE TABLE IF NOT EXISTS banned_files (
    `checksum`	VARCHAR(255)
);
CREATE TABLE IF NOT EXISTS users (
	`username`	VARCHAR(255),
	`password`	VARCHAR(255),
	`role`	VARCHAR(255) DEFAULT 'user'
);
INSERT INTO users (username, password, role) VALUES ("admin", "$2y$10$Pr.HHplV8Bc3eMvTVJaIxOmMyB8mAGmry7q0ggcjz5hm7rmvaSWSi", "admin");
CREATE TABLE IF NOT EXISTS board_banners (
	`id`	INT AUTO_INCREMENT,
	`image`	TEXT,
	`board`	TEXT,
	PRIMARY KEY(id)
);
CREATE TABLE IF NOT EXISTS board_config (
	`name`	VARCHAR(255),
	`custom_css_enabled`	INTEGER,
	`theme`	VARCHAR(255),
	`locked`	INTEGER,
	`country_flags_enabled`	INTEGER,
	`enable_ids`	INTEGER,
	`password` VARCHAR(255)
);
CREATE TABLE IF NOT EXISTS boards (
	`id`	INTEGER AUTO_INCREMENT,
	`name`	TEXT,
	`subtitle`	TEXT,
	`owner`	TEXT,
	`hidden`	INTEGER NULL,
	`post_count`	INTEGER DEFAULT 0,
	`pph` VARCHAR(50) DEFAULT '0',
	PRIMARY KEY(id)
);
CREATE TABLE IF NOT EXISTS bans (
	`ip`	VARCHAR(255),
	`reason`	VARCHAR(255),
	`board`	VARCHAR(255),
	`is_global`	INTEGER,
	`appeal_reason`	TEXT,
	`ban_on_post` INTEGER,
	`date_banned`	DATE
);
CREATE TABLE IF NOT EXISTS posts (
	`id`	INTEGER AUTO_INCREMENT,
	`name`	VARCHAR(255),
	`email`	VARCHAR(255),
	`subject`	VARCHAR(255),
	`message`	TEXT,
	`thread_id`	INTEGER,
	`type`	VARCHAR(255),
	`board`	VARCHAR(255),
	`files`	TEXT,
	`post_date`	int(11),
	`reply_count`	INTEGER DEFAULT 0,
	`password`	VARCHAR(255),
	`ip`	VARCHAR(255),
	`country_iso`	VARCHAR(2),
	`pinned`	INTEGER NULL,
	`locked`	INTEGER NULL,
	PRIMARY KEY(id)
);
CREATE TABLE IF NOT EXISTS board_vols (
	`name`	VARCHAR(255),
	`username`	VARCHAR(255)
);
CREATE TABLE IF NOT EXISTS emotes (
	`name`	VARCHAR(255),
	`url`	VARCHAR(255),
	`board` VARCHAR(255)
);
CREATE TABLE IF NOT EXISTS filters (
	`filter`	VARCHAR(255),
	`board`	VARCHAR(255),
	`result`	VARCHAR(255),
	`is_global`	INTEGER NULL,
	`ban_on_post` INTEGER NULL
);
