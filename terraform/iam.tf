# AWS Academy Learner Lab accounts CANNOT create IAM roles/instance profiles
# (the underlying LabRole denies iam:CreateRole etc). Reuse the profile that
# Academy already created for you instead of creating a new one.
data "aws_iam_instance_profile" "lab" {
  name = var.lab_instance_profile_name
}

# Fallback plain Amazon Linux 2023 AMI, only used if you didn't pass
# var.ami_id (i.e. you haven't baked your custom AMI into this var yet).
data "aws_ami" "al2023" {
  most_recent = true
  owners      = ["amazon"]

  filter {
    name   = "name"
    values = ["al2023-ami-*-x86_64"]
  }
}

locals {
  launch_ami_id = coalesce(var.ami_id, data.aws_ami.al2023.id)
}
