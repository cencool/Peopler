# Peopler

## Database of persons with web frontend based on Yii

For Yii framework learning purposes. Still under development

### Configuration

1. Clone the repository.
2. Run 'composer update' to download dependencies and the Yii framework itself.
3. Prepare empty mysql/mariadb database.
4. Create configuration file named "db.php" based on "db_example.php" with database connection credentials inside 'config' folder.
5. Create database tables needed using migrations. In terminal cd to app base directory (i.e. cd Peopler) and run ./yii migrate. This should create tables needed.
6. Create users using './yii user/create-user'. 
7. Run server (for example from app base directory you can './yii serve').
5. Connect (depending on your server configuration i.e. http://localhost:8080/index
