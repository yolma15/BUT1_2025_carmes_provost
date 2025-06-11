CREATE TABLE `boutiques` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nom` VARCHAR(50) UNIQUE NOT NULL,
  `utilisateur_id` INT NOT NULL,
  `numero_rue` VARCHAR(10) NOT NULL,
  `nom_adresse` VARCHAR(100) NOT NULL,
  `code_postal` VARCHAR(10) NOT NULL,
  `ville` VARCHAR(20) NOT NULL,
  `pays` VARCHAR(20) NOT NULL
);

CREATE TABLE `utilisateurs` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `num_tel` VARCHAR(20) UNIQUE,
  `role` VARCHAR(10) NOT NULL,
  `nom` VARCHAR(20) NOT NULL,
  `prenom` VARCHAR(20) NOT NULL,
  `ddn` DATE NOT NULL,
  `adresse` VARCHAR(255)
);

CREATE TABLE `stocks` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `quantite` INT,
  `date_de_modification` DATE,
  `confiserie_id` INT NOT NULL,
  `boutique_id` INT NOT NULL
);

CREATE TABLE `confiseries` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nom` VARCHAR(50) NOT NULL,
  `type` VARCHAR(20),
  `couleur` VARCHAR(20),
  `prix` FLOAT NOT NULL,
  `illustration` VARCHAR(255),
  `description` TEXT
);

ALTER TABLE `stocks` ADD FOREIGN KEY (`boutique_id`) REFERENCES `boutiques` (`id`);

ALTER TABLE `stocks` ADD FOREIGN KEY (`confiserie_id`) REFERENCES `confiseries` (`id`);

ALTER TABLE `boutiques` ADD FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);
