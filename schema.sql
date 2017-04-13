



USE snorby;

ALTER TABLE `sigs_to_block`
ADD `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
ADD `active` int(1) NOT NULL DEFAULT '1' AFTER `id`;
