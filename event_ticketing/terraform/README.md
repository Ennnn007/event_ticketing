# JLS Event Ticketing — Terraform (AWS Academy Learner Lab)

Reproduces the architecture you already built by hand in the console: VPC (2
public + 2 private subnets across `us-east-1a`/`us-east-1b`), 3 security
groups, an Aurora MySQL cluster, a launch template built from your custom
AMI, an Application Load Balancer + target group, and an Auto Scaling Group
with a target-tracking (CPU 60%) scaling policy.

## One-time setup in VS Code

1. Install the **HashiCorp Terraform** extension (Extensions icon → search
   "Terraform" → Install).
2. Open this `terraform/` folder in VS Code.
3. Copy `terraform.tfvars.example` → `terraform.tfvars`, then fill in:
   - `key_pair_name` — the EC2 key pair you already created
   - `ami_id` — the custom AMI ID you baked (`ami-...`), from EC2 → AMIs
   - `db_master_password` — a new password for `aws_admin`

## Every time you start a new Lab session

Your AWS Academy credentials expire when the Lab session ends, so before
running anything, open the Lab page → **AWS Details** → **AWS CLI**, copy the
3 values, and paste them into VS Code's terminal (`` Ctrl+` ``):

```bash
export AWS_ACCESS_KEY_ID="ASIA..."
export AWS_SECRET_ACCESS_KEY="..."
export AWS_SESSION_TOKEN="..."
```

## Running it

With the Terraform extension installed you get command-palette entries
(`Ctrl+Shift+P` → type "Terraform"), but the 3 you actually need are just
these terminal commands (the extension mainly gives you inline linting/
autocomplete on the `.tf` files, not one-click buttons for every command):

```bash
cd terraform
terraform init      # downloads the AWS provider, only needed once
terraform plan       # shows what will be created - review before applying
terraform apply       # type "yes" when prompted
```

When it finishes, `terraform output alb_dns_name` gives you the URL to open
in a browser (wait ~2-3 minutes after apply for instances to pass health
checks the first time).

## Pointing the app at Aurora

`config.php` in the app already reads `DB_HOST`/`DB_USER`/`DB_PASS`/`DB_NAME`
from environment variables — no code change needed. Set these on the EC2
instance (e.g. bake them into the AMI, or add an Apache `SetEnv` in
`/etc/httpd/conf.d/`) using:

```bash
terraform output aurora_cluster_endpoint   # -> DB_HOST
```

Then import the schema once from an instance inside the VPC:

```bash
mysql -h <aurora_cluster_endpoint> -u aws_admin -p event_ticketing_db < schema.sql
```

## Tearing down

Always destroy before your Lab session ends, or when you're done for the
day, so you don't burn Lab budget:

```bash
terraform destroy
```

## Notes specific to AWS Academy

- We reuse the existing **`LabInstanceProfile`** — the Lab role cannot
  create IAM roles/instance profiles, so don't try to add `aws_iam_role`
  resources here.
- No NAT Gateway is created (keeps cost/complexity down) — the private
  subnets have no outbound internet route. This is fine because your AMI is
  already fully configured; instances don't need to reach the internet at
  boot.
- `terraform.tfvars` is gitignored on purpose (it holds your DB password) —
  never commit it.
