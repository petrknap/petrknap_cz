CREATE TABLE url_lookup__keyword_to_url_map (
  id INT AUTO_INCREMENT NOT NULL,
  keyword VARCHAR(64) NOT NULL,
  title VARCHAR(256) DEFAULT NULL,
  url VARCHAR(2048) NOT NULL,
  proxy TINYINT(1) NOT NULL,
  PRIMARY KEY(id),
  UNIQUE (keyword)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

CREATE TABLE url_lookup__user_agents (
  hashed_user_agent BINARY(28) NOT NULL COMMENT 'use UNHEX(SHA2(input, 224))',
  user_agent  TEXT NOT NULL,
  PRIMARY KEY(hashed_user_agent)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

CREATE TABLE url_lookup__keyword_statistics (
  id INT AUTO_INCREMENT NOT NULL,
  keyword_to_url_map_id INT NOT NULL,
  hashed_user_agent BINARY(28) NULL COMMENT 'use UNHEX(SHA2(input, 224))',
  address INT UNSIGNED NULL COMMENT 'use INET_ATON/INET_NTOA',
  referrer VARCHAR(2048) NULL,
  touches INT NOT NULL DEFAULT 1,
  PRIMARY KEY(id),
  FOREIGN KEY (keyword_to_url_map_id) REFERENCES url_lookup__keyword_to_url_map(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (hashed_user_agent) REFERENCES url_lookup__user_agents(hashed_user_agent)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

CREATE VIEW url_lookup__records AS
  SELECT k2um.keyword, k2um.title, k2um.url, k2um.proxy,
    SUM(ks.touches) AS touches
  FROM url_lookup__keyword_to_url_map k2um
  LEFT JOIN url_lookup__keyword_statistics ks ON ks.keyword_to_url_map_id = k2um.id
  GROUP BY k2um.id;

-- TODO create view url_lookup__statistics
