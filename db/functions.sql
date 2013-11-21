
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `countUnread`(_userId INT, _feedId INT, _tagId INT, _maxCount INT) RETURNS int(11)
BEGIN

#DECLARE total INT;

#SET total = (
RETURN (
        SELECT 
				COUNT(1) AS total FROM ( 
			    	SELECT 1 
			    	FROM users_entries FORCE INDEX (indexUnread)
			    	WHERE feedId 	    = _feedId
					AND   userId	 	    = _userId
					AND   tagId			 = _tagId
			    	AND   entryRead 	 = false 
					LIMIT _maxCount
			) AS tmp); 

#RETURN total;
END ;;
DELIMITER ;


