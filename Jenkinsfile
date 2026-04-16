pipeline {
    agent any

    environment {
        DEPLOY_HOST = sh(script: "[ \${GIT_BRANCH} = 'origin/main' ] && echo '76.13.22.222' || echo '76.13.22.222'", returnStdout: true).trim()
        DEPLOY_USER = 'root'
        DEPLOY_PATH = '/var/www/freecause/backend'
    }

    stages {
        stage('Build') {
            steps {
                sh 'npm ci && npm run build'
            }
        }

        stage('Deploy') {
            steps {
                sshagent(['vps-ssh-key']) {
                    sh '''
                        ssh -o StrictHostKeyChecking=no $DEPLOY_USER@$DEPLOY_HOST "
                            cd $DEPLOY_PATH &&
                            git checkout -- . &&
                            git pull origin main &&
                            composer install --no-dev --optimize-autoloader &&
                            php artisan migrate --force &&
                            php artisan storage:link --force &&
                            php artisan optimize:clear &&
                            php artisan optimize &&
                            sudo systemctl restart php8.2-fpm &&
                            sudo supervisorctl restart freecause-queue:* &&
                            sleep 5 &&
                            curl -sf https://${DEPLOY_HOST}/up || exit 1
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
