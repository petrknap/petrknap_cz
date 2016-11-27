CREATE TABLE backup__hashed_files (
  hashed_path BINARY(36) NOT NULL COMMENT 'SHA1.MD5 - use UNHEX/HEX',
  hashed_content BINARY(20) NOT NULL COMMENT 'SHA1 - use UNHEX/HEX',
  PRIMARY KEY(hashed_path)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM;
