import tensorflow as tf
import pandas as pd
import os

# Detect path based on SageMaker environment
input_dir = "/opt/ml/input/data/training"
local_path = "../data/processed_data_scaled.csv"
data_path = os.path.join(input_dir, "processed_data_scaled.csv") if os.path.exists(input_dir) else local_path

df = pd.read_csv(data_path)
X = df.drop(columns=["champion", "result"])
y = df["result"]

model = tf.keras.Sequential([
    tf.keras.layers.Input(shape=(X.shape[1],)),
    tf.keras.layers.Dense(128, activation="relu"),
    tf.keras.layers.BatchNormalization(),
    tf.keras.layers.Dropout(0.3),
    tf.keras.layers.Dense(64, activation="relu"),
    tf.keras.layers.BatchNormalization(),
    tf.keras.layers.Dropout(0.3),
    tf.keras.layers.Dense(1, activation="sigmoid")
])

model.compile(optimizer="adam", loss="binary_crossentropy", metrics=["accuracy"])
model.fit(X, y, epochs=20, batch_size=32, validation_split=0.2)
model.save("/opt/ml/model")
print("✅ Model training complete and saved.")