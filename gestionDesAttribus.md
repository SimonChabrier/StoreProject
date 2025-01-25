

- le produit 
- - lié à une catégorie d'attributs (ex: couleur, taille, poids, etc)
- - - lié à un type d'attribut (ex: taille, poids, etc)
- ---- lié à plusieurs valeurs (ex : taille: 1m, 1m10, 1m20, etc)
- ------ Associé au produit (ex: taille: 1m, 1m10, 1m20, etc)

Pour faire ça j'ai créé 3 tables : 

  * product
  * attribute_type
  * attribute_value

La table attribute_type est lié à la table product par une relation many to many.
parce que un produit peut avoir plusieurs types d'attributs (ex: couleur, taille, poids, etc)

La table attribute_value est lié à la table attribute_type par une relation many to many parce que un type d'attribut peut avoir plusieurs valeurs (ex : taille: 1m, 1m10, 1m20, etc)


product AS 0/N attribute_type et attribute_type AS 0/N product

attribute_type AS 0/N attribute_value et attribute_value AS 0/1 attribute_type




