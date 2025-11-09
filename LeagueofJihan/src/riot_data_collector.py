import requests
import pandas as pd
import time, os, json
from config import RIOT_API_KEY, REQUEST_LIMIT, TIME_WINDOW, DATA_PATH

HEADERS = {"X-Riot-Token": RIOT_API_KEY}

def region_to_routing(region):
    if region in ["na1", "br1", "la1", "la2"]:
        return "americas"
    elif region in ["euw1", "eun1", "tr1", "ru"]:
        return "europe"
    elif region in ["kr", "jp1"]:
        return "asia"
    elif region == "oc1":
        return "sea"
    return "americas"

def get_puuid(game_name, tag_line, routing):
    url = f"https://{routing}.api.riotgames.com/riot/account/v1/accounts/by-riot-id/{game_name}/{tag_line}"
    r = requests.get(url, headers=HEADERS)
    time.sleep(TIME_WINDOW / REQUEST_LIMIT)
    if r.status_code == 200:
        return r.json()["puuid"]
    return None

def get_match_ids(puuid, routing, count=20):
    url = f"https://{routing}.api.riotgames.com/lol/match/v5/matches/by-puuid/{puuid}/ids?count={count}"
    r = requests.get(url, headers=HEADERS)
    time.sleep(TIME_WINDOW / REQUEST_LIMIT)
    return r.json() if r.status_code == 200 else []

def get_match_timeline(match_id, routing):
    url = f"https://{routing}.api.riotgames.com/lol/match/v5/matches/{match_id}/timeline"
    r = requests.get(url, headers=HEADERS)
    time.sleep(TIME_WINDOW / REQUEST_LIMIT)
    return r.json() if r.status_code == 200 else {}

def collect_data():
    os.makedirs(f"{DATA_PATH}/match_data", exist_ok=True)
    df = pd.read_csv(f"{DATA_PATH}/league - Sheet1.csv")
    puuids = []

    for idx, row in df.iterrows():
        champion = row["Champion"]
        for col in ["#1", "#2", "#3"]:
            riot_id = str(row[col])
            if pd.isna(riot_id) or "#" not in riot_id:
                continue

            game_name, tag = riot_id.split("#")
            region = "na1"  # or infer from tag if stored
            routing = region_to_routing(region)

            puuid = get_puuid(game_name, tag, routing)
            if not puuid:
                continue

            puuids.append({"Champion": champion, "Player": riot_id, "PUUID": puuid})

            match_ids = get_match_ids(puuid, routing)
            for m in match_ids:
                timeline = get_match_timeline(m, routing)
                with open(f"{DATA_PATH}/match_data/{m}.json", "w", encoding="utf-8") as f:
                    json.dump(timeline, f)

    pd.DataFrame(puuids).to_csv(f"{DATA_PATH}/puuids.csv", index=False)

if __name__ == "__main__":
    collect_data()
