<?php

class Config {
// Depricated const
    const AUTH_TRANSACTION = 1;
    const AUTH_REPORTING = 2;
    const AUTH_ADMIN = 3;
    const AUTH_LAMINATE_SEARCH = 4;
    const AUTH_WAREHOUSE = 5;

    //New User Access Rights Constants
    
    //Products related
    const USER_ACCESS_RIGHTS_LAMINATE_SEARCH = "1001"; //Default should be true
    const USER_ACCESS_RIGHTS_ORDER_PRODUCTS = "1002"; //Default should be true
    const USER_ACCESS_RIGHTS_SEARCH_PRODUCTS = "1003"; //Default should be true
    const USER_ACCESS_RIGHTS_CREATE_MY_STOCK = "1004";
    const USER_ACCESS_RIGHTS_CREATE_UPDATE_ITEMS = "1005";
    const USER_ACCESS_RIGHTS_CREATE_UPDATE_SIZES = "1006";
    const USER_ACCESS_RIGHTS_STOCK_INFO = "1007";
    const USER_ACCESS_RIGHTS_READ_LAMINATE_MANUFACTURER_LIST = "1008"; //Default should be true
    const USER_ACCESS_RIGHTS_CREATE_UPDATE_LAM_MANUFACTURER = "1009";
    const USER_ACCESS_RIGHTS_READ_ITEM_MASTER = "1010"; //Default should be true
    const USER_ACCESS_RIGHTS_READ_SIZES = "1011"; //Default should be true
    const USER_ACCESS_RIGHTS_GET_COMPANION_CODE = "1012"; //Default should be true
    const USER_ACCESS_RIHGTS_LAMINATE_MATCH_CREATE_STAGING = "1013";
    const USER_ACCESS_RIGHTS_STAGING_APPROVAL = "1014";
    const USER_ACCESS_RIGHTS_SINGLE_ITEM_UPDATE = "1015";
    const USER_ACCESS_RIGHTS_ORDER_CREATION = "1016"; //Default should be true
    const USER_ACCESS_RIGHTS_ORDER_REPORTING = "1017";
    
    //Users related
    const USER_ACCESS_RIGHTS_CREATE_UPDATE_NEWS = "2001";
    const USER_ACCESS_RIGHTS_ACCESS_INVOICE = "2002";
    const USER_ACCESS_RIGHTS_CREATE_UPDATE_USER = "2003";
    const USER_ACCESS_RIGHTS_CREATE_UPDATE_COMPANIES = "2004";
    const USER_ACCESS_RIGHTS_NEWS_FLASH = "2005"; //Default should be true
    const USER_ACCESS_RIGHTS_READ_ACCESS_RIGHTS = "2006"; //Default should be true
    const USER_ACCESS_RIGHTS_ADMIN_LOGIN = "2007";
    const USER_ACCESS_RIGHTS_UPDATE_ACCESS_RIGHTS = "2008";
    const USER_ACCESS_RIGHTS_CREATE_ACCESS_RIGHTS = "2009";
    
    //Reports
    const USER_ACCESS_RIGHTS_VIEW_REPORTS = "3001";
    
    //Warehouse
    const USER_ACCESS_RIGHTS_WAREHOUSE = "4001";
    const USER_ACCESS_RIGHTS_ORDER_LIFECYCLE_UPDATE = "4002";

    //Return value constants
    const ERROR_LOGIN_AGAIN = -1001;
    const ERROR_NO_ACCESS_RIGHTS = -1002;
    const ERROR_INVALID_CREDENTIALS = -1003;
    
    //Database connection parameters
    const DATABASE_HOST = '127.0.0.1';
    const DATABASE_USER = 'stock';
    const DATABASE_PASS = 'stocktesting#';
    const DATABASE_NAME = 'products';
}
?>
