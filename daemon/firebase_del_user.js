//npm install firebase-admin --save
//export GOOGLE_APPLICATION_CREDENTIALS="/var/www/seteAPI/config/autoload/google.local.json"

// Add the Firebase products that you want to use
var admin = require('firebase-admin');


admin.initializeApp({
  credential: admin.credential.applicationDefault(),
  databaseURL: 'https://softwareter.firebaseio.com'
});


admin.auth().deleteUser('GyjQ5WkfiYMZIG7Guq7yl9MqZiA3')
        .then(() => {
    console.log('Successfully deleted user');
  })
  .catch((error) => {
    console.log('Error deleting user:', error);
  });



