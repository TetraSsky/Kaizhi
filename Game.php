<?php

    class Game {
        private string $gameid;
        private string $titre;
        private string $description;
        private int $prix;
        private string $image;
        private string $liensteam;

        /**
         * Get the value of gameid
         */ 
        public function getGameid()
        {
                return $this->gameid;
        }

        /**
         * Set the value of gameid
         *
         * @return  self
         */ 
        public function setGameid($gameid)
        {
                $this->gameid = $gameid;

                return $this;
        }

        /**
         * Get the value of titre
         */ 
        public function getTitre()
        {
                return $this->titre;
        }

        /**
         * Set the value of titre
         *
         * @return  self
         */ 
        public function setTitre($titre)
        {
                $this->titre = $titre;

                return $this;
        }

        /**
         * Get the value of description
         */ 
        public function getDescription()
        {
                return $this->description;
        }

        /**
         * Set the value of description
         *
         * @return  self
         */ 
        public function setDescription($description)
        {
                $this->description = $description;

                return $this;
        }

        /**
         * Get the value of prix
         */ 
        public function getPrix()
        {
                return $this->prix;
        }

        /**
         * Set the value of prix
         *
         * @return  self
         */ 
        public function setPrix($prix)
        {
                $this->prix = $prix;

                return $this;
        }

        /**
         * Get the value of image
         */ 
        public function getImage()
        {
                return $this->image;
        }

        /**
         * Set the value of image
         *
         * @return  self
         */ 
        public function setImage($image)
        {
                $this->image = $image;

                return $this;
        }

        /**
         * Get the value of liensteam
         */ 
        public function getLiensteam()
        {
                return $this->liensteam;
        }

        /**
         * Set the value of liensteam
         *
         * @return  self
         */ 
        public function setLiensteam($liensteam)
        {
                $this->liensteam = $liensteam;

                return $this;
        }
    }