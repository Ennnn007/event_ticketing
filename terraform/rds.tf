resource "aws_db_subnet_group" "main" {
  name       = "${var.project_name}-db-subnet-group"
  subnet_ids = aws_subnet.private[*].id

  tags = { Name = "${var.project_name}-db-subnet-group" }
}

# Aurora MySQL cluster (matches "Standard create" -> Aurora MySQL-Compatible
# -> Sandbox template you picked in the console). Sandbox = no Multi-AZ
# standby replica, single instance, no deletion protection.
resource "aws_rds_cluster" "main" {
  cluster_identifier      = "${var.project_name}-aurora"
  engine                  = "aurora-mysql"
  engine_mode             = "provisioned"
  database_name           = var.db_name
  master_username         = var.db_master_username
  master_password         = var.db_master_password
  db_subnet_group_name    = aws_db_subnet_group.main.name
  vpc_security_group_ids  = [aws_security_group.rds.id]
  skip_final_snapshot     = true
  deletion_protection     = false
  apply_immediately       = true
  backup_retention_period = 1
}

resource "aws_rds_cluster_instance" "main" {
  identifier         = "${var.project_name}-aurora-instance-1"
  cluster_identifier = aws_rds_cluster.main.id
  engine             = aws_rds_cluster.main.engine
  engine_version      = aws_rds_cluster.main.engine_version
  instance_class     = var.db_instance_class
  db_subnet_group_name = aws_db_subnet_group.main.name
  publicly_accessible = false
}
