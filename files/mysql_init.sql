CREATE DATABASE IF NOT EXISTS Educacio;
USE Educacio;

CREATE TABLE equipaments_educacio (
    register_id INTEGER PRIMARY KEY,
    name TEXT,
    institution_id TEXT,
    institution_name TEXT,
    created TEXT,
    modified TEXT,
    addresses_roadtype_id TEXT,
    addresses_roadtype_name TEXT,
    addresses_road_id INTEGER,
    addresses_road_name TEXT,
    addresses_start_street_number TEXT,
    addresses_end_street_number TEXT,
    addresses_neighborhood_id TEXT,
    addresses_neighborhood_name TEXT,
    addresses_district_id TEXT,
    addresses_district_name TEXT,
    addresses_zip_code TEXT,
    addresses_town TEXT,
    addresses_main_address INTEGER,
    addresses_type TEXT,
    values_id INTEGER,
    values_attribute_id INTEGER,
    values_category TEXT,
    values_attribute_name TEXT,
    values_value TEXT,
    values_outstanding INTEGER,
    values_description TEXT,
    secondary_filters_id INTEGER,
    secondary_filters_name TEXT,
    secondary_filters_fullpath TEXT,
    secondary_filters_tree TEXT,
    secondary_filters_asia_id TEXT,
    geo_epgs_25831_x REAL,
    geo_epgs_25831_y REAL,
    geo_epgs_4326_lat REAL,
    geo_epgs_4326_lon REAL,
    estimated_dates TEXT,
    start_date TEXT,
    end_date TEXT,
    timetable TEXT
);

LOAD DATA LOCAL INFILE '/home/isard/equipaments_utf8.csv'
INTO TABLE equipaments_educacio
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ','
OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(@register_id, name, institution_id, institution_name, created, modified,
 addresses_roadtype_id, addresses_roadtype_name, addresses_road_id,
 addresses_road_name, addresses_start_street_number, addresses_end_street_number,
 addresses_neighborhood_id, addresses_neighborhood_name, addresses_district_id,
 addresses_district_name, addresses_zip_code, addresses_town,
 @addresses_main_address, addresses_type, values_id, values_attribute_id,
 values_category, values_attribute_name, values_value, @values_outstanding,
 values_description, secondary_filters_id, secondary_filters_name,
 secondary_filters_fullpath, secondary_filters_tree, secondary_filters_asia_id,
 @geo_epgs_25831_x, @geo_epgs_25831_y, @geo_epgs_4326_lat, @geo_epgs_4326_lon,
 estimated_dates, start_date, end_date, timetable)
SET
 register_id = TRIM(LEADING 0xEFBBBF FROM @register_id),
 addresses_main_address = IF(@addresses_main_address = 'True', 1, 0),
 values_outstanding = IF(@values_outstanding = 'True', 1, 0),
 geo_epgs_25831
