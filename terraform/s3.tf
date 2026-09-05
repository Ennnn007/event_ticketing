# S3 bucket is managed manually via AWS CLI due to a Learner Lab SCP that
# blocks s3:GetBucketObjectLockConfiguration, which the Terraform AWS
# provider (5.x) always calls when creating/reading this resource.
# The bucket, public access block, and policy are created manually — see
# project notes for the exact CLI commands used.

data "aws_caller_identity" "current" {}

data "aws_s3_bucket" "uploads" {
  bucket = "${var.project_name}-uploads-${data.aws_caller_identity.current.account_id}"
}
