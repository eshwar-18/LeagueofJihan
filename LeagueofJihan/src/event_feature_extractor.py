import os, json, pandas as pd
from config import DATA_PATH

def extract_features():
    data = []
    folder = f"{DATA_PATH}/match_data"

    for file in os.listdir(folder):
        path = os.path.join(folder, file)
        with open(path, "r", encoding="utf-8") as f:
            timeline = json.load(f)

        if "info" not in timeline:
            continue

        frames = timeline["info"]["frames"]
        kills = sum(len(f.get("events", [])) for f in frames)
        objectives = sum(1 for f in frames for e in f.get("events", []) if "Objective" in e.get("type", ""))
        game_duration = len(frames)
        data.append({"MatchID": file.replace(".json",""), "Kills": kills, "Objectives": objectives, "Frames": game_duration})

    pd.DataFrame(data).to_csv(f"{DATA_PATH}/processed_data.csv", index=False)

if __name__ == "__main__":
    extract_features()
