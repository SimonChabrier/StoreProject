<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230128193127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sub_category_product_type (sub_category_id INT NOT NULL, product_type_id INT NOT NULL, INDEX IDX_F0BEE0BF7BFE87C (sub_category_id), INDEX IDX_F0BEE0B14959723 (product_type_id), PRIMARY KEY(sub_category_id, product_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sub_category_product_type ADD CONSTRAINT FK_F0BEE0BF7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sub_category_product_type ADD CONSTRAINT FK_F0BEE0B14959723 FOREIGN KEY (product_type_id) REFERENCES product_type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sub_category_product_type DROP FOREIGN KEY FK_F0BEE0BF7BFE87C');
        $this->addSql('ALTER TABLE sub_category_product_type DROP FOREIGN KEY FK_F0BEE0B14959723');
        $this->addSql('DROP TABLE sub_category_product_type');
    }
}
