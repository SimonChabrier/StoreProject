# Product 

- id (integer) - id of the product (autoincremented) (required)
- name (string) - name of the product (required) - max length: 255
- barcode (string) - barcode of the product (nullable) - max length: 50
- reference (string) - internal reference of the product (required) - max length: 50
- catalogPrice (sting) - catalog price of the product (nullable) - max length: 10
- sellingPrice (string) - selling price of the product (required) - max length: 10
- stockQuantity (string) - stock of the product (required) - max length: 10 - default: 0
- inCustomerOrder (string) - quantity of the product in customer order (required) - max length: 10 - default: 0
- inSupplierOrder (string) - quantity of the product in supplier order (required) - max length: 10 - default: 0
- discountPercentage (string) - discount percentage of the product (required) - max length: 10 - default: 0
- avalable (boolean) - if the product is available (required) - default: true
- quantityDiscount (boolean) - if the product has quantity discount (required) - default: false
- quantityDiscoutPercentage (string) - quantity for the discount (required) - max length: 2 - default: 0
- minimalQuantityForDiscount (string) - minimal quantity for the discount (required) - max length: 5 - default: 0
- shortDescription (text) - short description of the product (required) - max length: 255
- longDescription (text) - long description of the product (required)
- mainPicture (string) - main picture of the product (nullable) - max length: 255
- createdAt (datetime_immutable) - date of creation of the product (required)
- updatedAt (datetime) - date of update of the product (required)
- 
- provider (relation) - provider of the product (nullable) - relation: Provider manyToMnay each Product has Many Provider and each Provider has Many Product

# User

- id (integer) - id of the user (autoincremented) (required)
- firstName (string) - first name of the user (required) - max length: 50
- lastName (string) - last name of the user (required) - max length: 50
- gender (boolean) - gender of the user (required) - default: true (0 = Male / 1 = Female)
- billingAddress (string) - billing address of the user (required) - max length: 255
- addDeliveryAddress (boolean) - if the user has a delivery address (required) - default: false
- deliveryAddress (string) - delivery address of the user (required) - max length: 255
- phone (string) - phone number of the user (required) - max length: 20
- email (string) - email of the user (required) - max length: 100
- createdAt (datetime_immutable) - date of creation of the order (required)
- updatedAt (datetime) - date of update of the order (required)
- 
- orders (relation) - orders of the user (nullable) - relation: Order OneToMany each User has Many Order and each Order has One User

# Provider 

- id (integer) - id of the order (autoincremented) (required)
- companyName (string) - company name of the provider (required) - max length: 50
- adress (string) - adress of the provider (required) - max length: 255
- contactName (string) - contact name of the provider (required) - max length: 50
- email (string) - mail of the provider (required) - max length: 100
- phone (string) - phone number of the provider (required) - max length: 20
- createdAt (datetime_immutable) - date of creation of the order (required)
- updatedAt (datetime) - date of update of the order (required)
- 
- products (relation) - products of the provider (nullable) - relation: Product ManyToMany each Provider has Many Product and each Product has Many Provider

# Order

- id (integer) - id of the order (autoincremented) (required)
- orderNumber (string) - order number of the order (required) - max length: 50
- status (string) - status of the order (required) - max length: 50
- createdAt (datetime_immutable) - date of creation of the order (required)
- updatedAt (datetime) - date of update of the order (required)
- 
- user (relation) - user of the order (nullable) - relation: User OneToMany each Order has One User and each User has Many Order
- products (relation) - products of the order (nullable) - relation: Product ManyToMany each Order has Many Product and each Product has Many Order