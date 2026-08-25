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
}
