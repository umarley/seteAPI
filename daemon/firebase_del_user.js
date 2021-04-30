//npm install firebase-admin --save
//export GOOGLE_APPLICATION_CREDENTIALS="/var/www/seteAPI/config/autoload/google.local.json"
//Recebe o argumento
const argumento = process.argv.slice(2);

// Add the Firebase products that you want to use
var admin = require('firebase-admin');

admin.initializeApp({
  credential: admin.credential.applicationDefault(),
  databaseURL: 'https://softwareter.firebaseio.com'
});
//Extrai o UId a ser deletado do argumento recebido
const [uid] = argumento;

admin.auth().deleteUser(uid)
        .then(() => {
    console.log('{"result": true}');
  })
  .catch((error) => {
    console.log(error.errorInfo);
  });



