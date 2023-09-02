/* groovylint-disable-next-line CompileStatic */
node {
    /* groovylint-disable-next-line NoDef, VariableTypeRequired */
    try {
        stage('Clone Repo') {
            git branch: 'develop',
            credentialsId: '974bb4d4-016a-4f05-82a9-ea55e365055b',
            poll: false,
            url: 'git@gitlab.geminico.cloud:event-show/laravel-api.git'
        }
        stage('Build docker') {
           sh 'cp .env.dev .env'
           sh 'make build'
        }
        stage('Deploy docker') {
            try{
                sh 'make stop'
            }catch(e){
                // pass
            }
            sh 'make start'
        }
        stage('Laravel post deploy') {
            sh 'make env-dev'
            sh 'make composer-install'
            // sh 'sleep 120'
            // sh 'make exec cmd='php artisan storage:link''
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
