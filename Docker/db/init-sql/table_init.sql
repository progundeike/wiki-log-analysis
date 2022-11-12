DROP TABLE IF EXISTS page_views;

CREATE TABLE page_views (
  domain_code VARCHAR(255),
  page_title MEDIUMTEXT,
  count_views INTEGER,
  total_response_size INTEGER
);
