<?php
return array(
    "balise_a_pas_prendre" => array(
        "root"
    ),
    "balise_a_prendre" => array(
        "pays" => array(
            "correspondance" =>  "pays",
            "libelle" =>  "Pays",
            "factoryclasse" => "CountryTable",
            "classe" => "Country",
            "attribute" => array(
                "Id" => "reference",
                "Nom" => "label_origin",
                "Abreviation" => "iso",
            )
        ),
        "region" => array(
            "correspondance" =>  "region",
            "libelle" =>  "Region",
            "factoryclasse" => "RegionTable",
            "classe" => "Region",
            "attribute" => array(
                "Id" => "reference",
                "Nom" => "label_origin")
        ),
        "decoupage" => array(
            "correspondance" =>  "departement",
            "libelle" =>  "Decoupage",
            "factoryclasse" => "DepartmentTable",
            "classe" => "Department",
            "attribute" => array(
                "Id" => "reference",
                "Code" => "code",
                "Nom" => "label_origin")
        ),
        "commune" => array(
            "correspondance" =>  "commune",
            "libelle" =>  "Commune",
            "factoryclasse" => "CityTable",
            "classe" => "City",
            "attribute" => array(
                "Id" => "reference",
                "Insee" => "insee",
                "NomClassement" => "label_origin",
                "Situation" => "situation_origin")
        ),
        "site" => array(
            "correspondance" =>  "camping",
            "libelle" =>  "Site",
            "factoryclasse" => "CampingTable",
            "classe" => "Camping",
            "attribute" => array(
                "Id" => "reference",
                "Nom" => "name",
                "Latitude" => "coord_latitude",
                "Longitude" => "coord_longitude",
                "Acces" => "access",
                "CpGeo" => "zip_code",
                "AdresseGeo" => "address",
                "ComplAdresseGeo" => "address2"),
            "attribute_join" => array(
               
            )   
        ),
    )
);
