CREATE TABLE IF NOT EXISTS /*_*/fbpixelid_map (
	`id` int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`page_id` int NOT NULL,
	`pixel_id` varchar(100) NOT NULL
);