<?php

if(!defined('PACKAGE_PRODUCT_LIEN_INTERNET'))define('PACKAGE_PRODUCT_LIEN_INTERNET', 1);
if(!defined('PACKAGE_THEMATIQUE_LOISIRS_FAVORIS'))define('PACKAGE_THEMATIQUE_LOISIRS_FAVORIS', 3);
if(!defined('PACKAGE_THEMATIQUE_SITES_TOURISTIQUE'))define('PACKAGE_THEMATIQUE_SITES_TOURISTIQUE', 4);
if(!defined('PACKAGE_PRODUCT_COUP_COEUR'))define('PACKAGE_PRODUCT_COUP_COEUR', 5);
if(!defined('PACKAGE_PRODUCT_SELECTION'))define('PACKAGE_PRODUCT_SELECTION', 6);
if(!defined('PACKAGE_PRODUCT_VIDEO'))define('PACKAGE_PRODUCT_VIDEO', 7);
if(!defined('PACKAGE_PRODUCT_BON'))define('PACKAGE_PRODUCT_BON', 8);
if(!defined('PACKAGE_PRODUCT_RESERVATION'))define('PACKAGE_PRODUCT_RESERVATION', 11);

// year for millesime calcul
if(! defined('MILLESIME_YEAR'))define("MILLESIME_YEAR", 2017);

// URL dui logo reduction
if(! defined('URL_LOGO_REDUC')) define('URL_LOGO_REDUC', "media/pictos/caracteristics/reduction.png");

if(!defined('TYPE_CONTRACT_LIEN_INTERNET'))define('TYPE_CONTRACT_LIEN_INTERNET', serialize(array("basic" => 1, "valorize" => 10)));

if(!defined('TYPE_MEDIA_PHOTO'))define('TYPE_MEDIA_PHOTO', 2);
if(!defined('TYPE_MEDIA_VIDEO'))define('TYPE_MEDIA_VIDEO', 1);

if(!defined('ROLE_GESTIONNAIRE_CAMPING'))define('ROLE_GESTIONNAIRE_CAMPING', 6);

if(!defined('MILLESIME_TARANC'))define('MILLESIME_TARANC', 2016);
if(!defined('MILLESIME_TARNOUV'))define('MILLESIME_TARNOUV', 2017);