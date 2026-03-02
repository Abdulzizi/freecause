pipeline {
    agent any

    environment {
        DEPLOY_HOST = '76.13.22.222'
        DEPLOY_USER = 'root'
        DEPLOY_PATH = '/var/www/freecause/backend'
    }

    stages {
        stage('Deploy') {
            steps {
                sshagent(['vps-ssh-key']) {
                    sh '''
                        ssh -o StrictHostKeyChecking=no $DEPLOY_USER@$DEPLOY_HOST "
                            cd $DEPLOY_PATH &&
                            git pull origin main &&
                            composer install --no-dev --optimize-autoloader &&
                            php artisan migrate --force &&
                            php artisan optimize:clear &&
                            php artisan optimize &&
                            sudo systemctl restart php8.2-fpm
                        "
                    '''
                }
            }
        }
    }

    post {
        success {
            echo 'Deployed successfully'
        }
        failure {
            echo 'Deploy failed - check logs'
        }
    }
}
