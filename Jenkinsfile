/* groovylint-disable-next-line CompileStatic */
node {
    /* groovylint-disable-next-line NoDef, VariableTypeRequired */
    try {
        stage('Clone Repo') {
            git branch: 'main',
            credentialsId: 'ed641ed3-eca4-4802-b99c-e364e72b4d05',
            poll: false,
            url: 'git@gitlab.com:gemini-and-co/event-show/laravel-api.git'
        }
        stage('Build docker') {
           sh 'docker compose build --no-cache'
        }
        stage('Deploy docker') {
            sh 'docker compose down'
            sh 'docker compose up -d'
            sh 'docker compose exec app chown -R www-data:www-data /var/www/storage'
        }
    } catch (e) {
        currentBuild.result = 'FAILED'
        throw e
    /* groovylint-disable-next-line EmptyFinallyBlock */
    } finally {
        if (currentBuild.result != 'FAILURE') {
        }
    }
}
