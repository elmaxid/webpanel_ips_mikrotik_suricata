



USE snorby;

ALTER TABLE `block_queue`
ADD `que_event_cid` int(10) NULL AFTER `que_sig_sid`;


ALTER TABLE `sigs_to_block`
ADD `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
ADD `active` int(1) NOT NULL DEFAULT '1' AFTER `id`;


DROP TRIGGER `after_iphdr_insert`;
DELIMITER ;;
CREATE TRIGGER `after_iphdr_insert` AFTER INSERT ON `iphdr` FOR EACH ROW
BEGIN
                        DECLARE this_event INT(11) default 0;
                        DECLARE this_event_signature INT(10) default 0;
                        DECLARE this_event_timestamp TIMESTAMP;
                        DECLARE this_sig INT(10) default 0;
                        DECLARE this_sig_name VARCHAR(256) default "";
                        DECLARE this_sig_gid INT(10) default 0;

  						DECLARE this_que_event_cid INT(10) default 0;

                        DECLARE timeout VARCHAR(12) default "";
                        DECLARE interested INT default 0;
                        DECLARE direction VARCHAR(3) default "";
                        DECLARE ip_src VARCHAR(64) default "";
                        DECLARE ip_dst VARCHAR(64) default "";
                        SELECT event.id, event.signature, event.timestamp, event.cid
                        INTO this_event, this_event_signature, this_event_timestamp, this_que_event_cid
                        FROM event
                        WHERE event.sid = NEW.sid and event.cid = NEW.cid;  
                        SELECT signature.sig_sid, signature.sig_gid, signature.sig_name 
                        INTO this_sig, this_sig_gid, this_sig_name
                        FROM signature
                        WHERE signature.sig_id = this_event_signature;
                        SELECT count(*), sigs_to_block.src_or_dst, sigs_to_block.timeout
                        INTO interested, direction, timeout
                        FROM sigs_to_block
                        WHERE this_sig_name LIKE CONCAT(sigs_to_block.sig_name, '%');
                        IF (interested > 0) THEN
                         IF (direction = "src") THEN
                            INSERT INTO block_queue
                         SET que_ip_adr =NEW.ip_src,
                                que_timeout = timeout,
                                que_sig_name = this_sig_name,
                                que_sig_gid = this_sig_gid,
                                que_sig_sid = this_sig,
que_event_cid=this_que_event_cid,
                                que_event_timestamp = this_event_timestamp;
                          ELSE
                            INSERT INTO block_queue
                         SET que_ip_adr =NEW.ip_dst,
                                que_timeout = timeout,
                                que_sig_name = this_sig_name,
                                que_sig_gid = this_sig_gid,
                                que_sig_sid = this_sig,
que_event_cid=this_que_event_cid,
                                que_event_timestamp = this_event_timestamp;
                          END IF;
                        END IF;
                      END;;
DELIMITER ;