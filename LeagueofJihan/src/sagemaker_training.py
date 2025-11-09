import sagemaker
from sagemaker.sklearn.estimator import SKLearn
import boto3

role = "arn:aws:iam::<your-account-id>:role/service-role/AmazonSageMaker-ExecutionRole"
session = sagemaker.Session()

estimator = SKLearn(
    entry_point="train_model.py",
    source_dir="src",
    role=role,
    instance_count=1,
    instance_type="ml.t3.medium",
    framework_version="1.0-1",
    py_version="py3",
)

estimator.fit({"training": "s3://your-bucket/processed_data_scaled.csv"})
