resource "aws_launch_template" "app" {
  name_prefix   = "${var.project_name}-lt-"
  image_id      = local.launch_ami_id
  instance_type = var.instance_type
  key_name      = var.key_pair_name
  iam_instance_profile {
    name = data.aws_iam_instance_profile.lab.name
  }
  vpc_security_group_ids = [aws_security_group.ec2.id]
  tag_specifications {
    resource_type = "instance"
    tags = {
      Name = "${var.project_name}-instance"
    }
  }

  user_data = base64encode(<<-EOF
    #!/bin/bash
    if ! grep -q "S3_BUCKET" /etc/httpd/conf.d/env-vars.conf; then
      echo 'SetEnv S3_BUCKET "${data.aws_s3_bucket.uploads.id}"' >> /etc/httpd/conf.d/env-vars.conf
      echo 'SetEnv AWS_REGION "${var.aws_region}"' >> /etc/httpd/conf.d/env-vars.conf
    fi
    chown -R apache:apache /var/www/html/uploads
    chmod -R 755 /var/www/html/uploads
    systemctl restart httpd
  EOF
  )
}
