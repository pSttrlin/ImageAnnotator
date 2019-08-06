import os
import glob
import tempfile
from pdf2image import convert_from_path

with tempfile.TemporaryDirectory() as path:
    i = 0
    for file in glob.glob("*.PDF"):
        print(file)
        images_from_path = convert_from_path(file, output_folder=path,
                                            last_page=1, first_page=0,
                                            poppler_path='poppler-0.67.0/bin')
        save_dir = '../images'
        for page in images_from_path:
            savename = os.path.join(save_dir, "IMG" + str(i) + ".jpeg")
            page.save(savename, "JPEG")
        i+=1
