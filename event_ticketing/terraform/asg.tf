resource "aws_autoscaling_group" "app" {
  name                = "${var.project_name}-asg"
  vpc_zone_identifier = aws_subnet.private[*].id
  target_group_arns   = [aws_lb_target_group.app.arn]

  desired_capacity = var.asg_desired_capacity
  min_size         = var.asg_min_size
  max_size         = var.asg_max_size

  # ELB health checks (fails an instance out of service if the ALB health
  # check fails, not just an EC2 status check). EBS-volume health checks are
  # a newer console option; if this specific attribute isn't recognized by
  # your provider version, drop it and we'll rely on the ELB check alone.
  health_check_type         = "ELB"
  health_check_grace_period = var.instance_warmup_seconds
  default_instance_warmup   = var.instance_warmup_seconds

  launch_template {
    id      = aws_launch_template.app.id
    version = "$Latest"
  }

  tag {
    key                 = "Name"
    value               = "${var.project_name}-instance"
    propagate_at_launch = true
  }
}

resource "aws_autoscaling_policy" "cpu_target_tracking" {
  name                   = "${var.project_name}-cpu-target-tracking"
  autoscaling_group_name = aws_autoscaling_group.app.name
  policy_type            = "TargetTrackingScaling"

  target_tracking_configuration {
    predefined_metric_specification {
      predefined_metric_type = "ASGAverageCPUUtilization"
    }
    target_value = var.target_tracking_cpu_target
  }
}
