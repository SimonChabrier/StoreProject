<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230122105152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_type_product_attribute (product_type_id INT NOT NULL, product_attribute_id INT NOT NULL, INDEX IDX_6A2D720B14959723 (product_type_id), INDEX IDX_6A2D720B3B420C91 (product_attribute_id), PRIMARY KEY(product_type_id, product_attribute_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_type_product_attribute ADD CONSTRAINT FK_6A2D720B14959723 FOREIGN KEY (product_type_id) REFERENCES product_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_type_product_attribute ADD CONSTRAINT FK_6A2D720B3B420C91 FOREIGN KEY (product_attribute_id) REFERENCES product_attribute (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_type_product_attribute DROP FOREIGN KEY FK_6A2D720B14959723');
        $this->addSql('ALTER TABLE product_type_product_attribute DROP FOREIGN KEY FK_6A2D720B3B420C91');
        $this->addSql('DROP TABLE product_type_product_attribute');
    }
}
