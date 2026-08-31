output "alb_dns_name" {
  description = "Open this in a browser to reach the site once instances are healthy"
  value       = aws_lb.main.dns_name
}

output "aurora_cluster_endpoint" {
  description = "DB_HOST value for config.php (writer endpoint)"
  value       = aws_rds_cluster.main.endpoint
  sensitive   = false
}

output "aurora_reader_endpoint" {
  value = aws_rds_cluster.main.reader_endpoint
}

output "vpc_id" {
  value = aws_vpc.main.id
}

output "s3_bucket_name" {
  value = aws_s3_bucket.uploads.id
}

output "db_secret_name" {
  value = aws_secretsmanager_secret.db_credentials.name
}