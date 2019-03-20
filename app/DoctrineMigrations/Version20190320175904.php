<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190320175904 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sales_person (id INT AUTO_INCREMENT NOT NULL, salesPersonNumber VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1628837EB5B591F8 (salesPersonNumber), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product ADD bestSeller TINYINT(1) NOT NULL, CHANGE price price NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE fos_user ADD sales_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A64791D35E30E FOREIGN KEY (sales_person_id) REFERENCES sales_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A64791D35E30E ON fos_user (sales_person_id)');
        $this->addSql('ALTER TABLE customer ADD sales_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E091D35E30E FOREIGN KEY (sales_person_id) REFERENCES sales_person (id)');
        $this->addSql('CREATE INDEX IDX_81398E091D35E30E ON customer (sales_person_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user DROP FOREIGN KEY FK_957A64791D35E30E');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E091D35E30E');
        $this->addSql('DROP TABLE sales_person');
        $this->addSql('DROP INDEX IDX_81398E091D35E30E ON customer');
        $this->addSql('ALTER TABLE customer DROP sales_person_id');
        $this->addSql('DROP INDEX UNIQ_957A64791D35E30E ON fos_user');
        $this->addSql('ALTER TABLE fos_user DROP sales_person_id');
        $this->addSql('ALTER TABLE product DROP bestSeller, CHANGE price price NUMERIC(10, 2) DEFAULT NULL');
    }
}
