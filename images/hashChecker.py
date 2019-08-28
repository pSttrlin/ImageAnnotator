import hashlib
import os
import glob
from tqdm import tqdm

def compareFiles(file1, file2):
    hash1 = hashfile(file1)
    hash2 = hashfile(file2)

    return hash1 == hash2

def hashfile(file):
    with open(file, "rb") as f:
        hasher = hashlib.md5()
        buf = f.read(65536)
        while len(buf) > 0:
            hasher.update(buf)
            buf = f.read(65536)
        return hasher.hexdigest()

dups = {}

for file in glob.glob("*.jpeg"):
    file_hash = hashfile(file)
    if file_hash in dups:
        print("Moving duplicate {0} (Original: {1})".format(file, dups[file_hash]))
        os.rename(file, os.path.join("duplicates", file))
    else:
        dups[file_hash] = file
exit(0)
