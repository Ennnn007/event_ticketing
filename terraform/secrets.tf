resource "aws_secretsmanager_secret" "db_credentials" {
  name        = "${var.project_name}-db-credentials"
  description = "Aurora MySQL connection details for the event ticketing app"
}

resource "aws_secretsmanager_secret_version" "db_credentials" {
  secret_id = aws_secretsmanager_secret.db_credentials.id
  secret_string = jsonencode({
    host     = aws_rds_cluster.main.endpoint
    username = var.db_master_username
    password = var.db_master_password
    dbname   = "event_ticketing_db"
  })
}