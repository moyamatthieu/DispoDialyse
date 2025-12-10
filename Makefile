.PHONY: help up down init logs shell test fresh restart clean

help: ## Afficher l'aide
	@echo "DispoDialyse - Commandes Docker"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

up: ## Démarrer les conteneurs
	docker-compose up -d

down: ## Arrêter les conteneurs
	docker-compose down

init: up ## Installation complète initiale
	@echo "⏳ Attente que les services soient prêts..."
	@sleep 10
	docker-compose exec app bash .devcontainer/init.sh

logs: ## Voir les logs
	docker-compose logs -f app

shell: ## Accéder au shell du conteneur
	docker-compose exec app bash

test: ## Exécuter les tests
	docker-compose exec app php artisan test

fresh: ## Réinitialiser la base de données
	docker-compose exec app php artisan migrate:fresh --seed

restart: down up ## Redémarrer les conteneurs

clean: down ## Nettoyer complètement (supprime les volumes)
	docker-compose down -v
	@echo "✅ Tout est nettoyé !"

build: ## Reconstruire les images Docker
	docker-compose build --no-cache

ps: ## Afficher l'état des conteneurs
	docker-compose ps

serve: ## Démarrer le serveur Laravel
	docker-compose exec app php artisan serve --host=0.0.0.0 --port=8000

migrate: ## Exécuter les migrations
	docker-compose exec app php artisan migrate

seed: ## Exécuter les seeders
	docker-compose exec app php artisan db:seed

optimize: ## Optimiser l'application pour la production
	docker-compose exec app composer install --optimize-autoloader --no-dev
	docker-compose exec app npm run build
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

cache-clear: ## Vider tous les caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear