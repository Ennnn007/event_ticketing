variable "aws_region" {
  description = "AWS region (AWS Academy labs default to us-east-1)"
  type        = string
  default     = "us-east-1"
}

variable "availability_zones" {
  description = "The two AZs used throughout (public + private subnets, DB subnet group, ASG)"
  type        = list(string)
  default     = ["us-east-1a", "us-east-1b"]
}

variable "project_name" {
  description = "Prefix used to name/tag every resource"
  type        = string
  default     = "jls-event-ticketing"
}

variable "vpc_cidr" {
  description = "CIDR block for the VPC"
  type        = string
  default     = "10.10.0.0/16"
}

variable "public_subnet_cidrs" {
  description = "CIDRs for the 2 public subnets (used by the ALB)"
  type        = list(string)
  default     = ["10.10.1.0/24", "10.10.2.0/24"]
}

variable "private_subnet_cidrs" {
  description = "CIDRs for the 2 private subnets (used by EC2/ASG and RDS)"
  type        = list(string)
  default     = ["10.10.11.0/24", "10.10.12.0/24"]
}

variable "key_pair_name" {
  description = "Name of the EC2 key pair you already created in the AWS console (Academy Lab can't create key pairs cleanly via Terraform, so reuse the one you made)"
  type        = string
}

variable "instance_type" {
  description = "EC2 instance type for the launch template"
  type        = string
  default     = "t3.micro"
}

variable "ami_id" {
  description = "The custom AMI ID you baked manually from your configured EC2 instance (ami-xxxxxxxx). Leave null to fall back to the latest plain Amazon Linux 2023 AMI (you'd then need user_data to install the app yourself)."
  type        = string
  default     = null
}

variable "lab_instance_profile_name" {
  description = "Name of the pre-existing Academy Lab instance profile (do not try to create a new IAM role/profile — the Lab role can't create IAM resources)"
  type        = string
  default     = "LabInstanceProfile"
}

variable "db_name" {
  description = "Initial database name created inside the Aurora cluster"
  type        = string
  default     = "event_ticketing_db"
}

variable "db_master_username" {
  description = "Aurora MySQL master username"
  type        = string
  default     = "aws_admin"
}

variable "db_master_password" {
  description = "Aurora MySQL master password. Set this in terraform.tfvars (which must be gitignored) or via TF_VAR_db_master_password env var - never commit it."
  type        = string
  sensitive   = true
}

variable "db_instance_class" {
  description = "Aurora instance class (Academy Lab roles are usually restricted to t3/t4g burstable classes)"
  type        = string
  default     = "db.t3.medium"
}

variable "asg_desired_capacity" {
  type    = number
  default = 2
}

variable "asg_min_size" {
  type    = number
  default = 2
}

variable "asg_max_size" {
  type    = number
  default = 2
}

variable "target_tracking_cpu_target" {
  description = "Target average CPU % for the target-tracking scaling policy"
  type        = number
  default     = 60
}

variable "instance_warmup_seconds" {
  type    = number
  default = 300
}
