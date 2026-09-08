terraform {
  required_version = ">= 1.5.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.30"
    }
  }
}

# --- AWS Academy Learner Lab note ---
# Do NOT hardcode access_key/secret_key/token here. Every time you start the
# lab, "AWS Details" gives you a NEW access key, secret key and session token
# (they expire when the lab session ends). The provider below picks these up
# automatically from environment variables, so before every `terraform apply`
# run (in the same terminal):
#
#   export AWS_ACCESS_KEY_ID="ASIA..."
#   export AWS_SECRET_ACCESS_KEY="..."
#   export AWS_SESSION_TOKEN="..."
#
# (copy these 3 values from the Lab's "AWS Details" > "AWS CLI" panel)
provider "aws" {
  region = var.aws_region
}
