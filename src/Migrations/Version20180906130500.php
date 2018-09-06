<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180906130500 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, commented_by_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, commented_at DATETIME NOT NULL, INDEX IDX_9474526C94F6F716 (commented_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu_comment (menu_id INT NOT NULL, comment_id INT NOT NULL, INDEX IDX_1CAF4FD2CCD7E912 (menu_id), INDEX IDX_1CAF4FD2F8697D13 (comment_id), PRIMARY KEY(menu_id, comment_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C94F6F716 FOREIGN KEY (commented_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE menu_comment ADD CONSTRAINT FK_1CAF4FD2CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_comment ADD CONSTRAINT FK_1CAF4FD2F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE menu_comment DROP FOREIGN KEY FK_1CAF4FD2F8697D13');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE menu_comment');
    }
}
