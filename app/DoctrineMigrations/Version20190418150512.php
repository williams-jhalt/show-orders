<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190418150512 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE vendor (id INT AUTO_INCREMENT NOT NULL, vendorNumber VARCHAR(255) NOT NULL, company VARCHAR(255) NOT NULL, imageUrl VARCHAR(255) NOT NULL, booth VARCHAR(255) DEFAULT NULL, sponserhipLevel VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_F52233F6D42F4BE (vendorNumber), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, vendor_id INT DEFAULT NULL, itemNumber VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, imageUrl VARCHAR(255) NOT NULL, bestSeller TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_D34A04AD31A3ED2E (itemNumber), INDEX IDX_D34A04ADF603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_person (id INT AUTO_INCREMENT NOT NULL, salesPersonNumber VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1628837EB5B591F8 (salesPersonNumber), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user (id INT AUTO_INCREMENT NOT NULL, sales_person_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_957A6479C05FB297 (confirmation_token), UNIQUE INDEX UNIQ_957A64791D35E30E (sales_person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, sales_person_id INT DEFAULT NULL, customerNumber VARCHAR(255) NOT NULL, company VARCHAR(255) NOT NULL, firstName VARCHAR(255) DEFAULT NULL, lastName VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_81398E09D53183C5 (customerNumber), INDEX IDX_81398E091D35E30E (sales_person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE show_order (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, submitted TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_F798F7449395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE show_order_item (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, show_order_id INT DEFAULT NULL, quantity INT NOT NULL, INDEX IDX_5C011BA04584665A (product_id), INDEX IDX_5C011BA0B4FB8CD7 (show_order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_note (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, vendor_id INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_9B2C5E639395C3F3 (customer_id), INDEX IDX_9B2C5E63F603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADF603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id)');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A64791D35E30E FOREIGN KEY (sales_person_id) REFERENCES sales_person (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E091D35E30E FOREIGN KEY (sales_person_id) REFERENCES sales_person (id)');
        $this->addSql('ALTER TABLE show_order ADD CONSTRAINT FK_F798F7449395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE show_order_item ADD CONSTRAINT FK_5C011BA04584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE show_order_item ADD CONSTRAINT FK_5C011BA0B4FB8CD7 FOREIGN KEY (show_order_id) REFERENCES show_order (id)');
        $this->addSql('ALTER TABLE customer_note ADD CONSTRAINT FK_9B2C5E639395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE customer_note ADD CONSTRAINT FK_9B2C5E63F603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADF603EE73');
        $this->addSql('ALTER TABLE customer_note DROP FOREIGN KEY FK_9B2C5E63F603EE73');
        $this->addSql('ALTER TABLE show_order_item DROP FOREIGN KEY FK_5C011BA04584665A');
        $this->addSql('ALTER TABLE fos_user DROP FOREIGN KEY FK_957A64791D35E30E');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E091D35E30E');
        $this->addSql('ALTER TABLE show_order DROP FOREIGN KEY FK_F798F7449395C3F3');
        $this->addSql('ALTER TABLE customer_note DROP FOREIGN KEY FK_9B2C5E639395C3F3');
        $this->addSql('ALTER TABLE show_order_item DROP FOREIGN KEY FK_5C011BA0B4FB8CD7');
        $this->addSql('DROP TABLE vendor');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE sales_person');
        $this->addSql('DROP TABLE fos_user');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE show_order');
        $this->addSql('DROP TABLE show_order_item');
        $this->addSql('DROP TABLE customer_note');
    }
}
