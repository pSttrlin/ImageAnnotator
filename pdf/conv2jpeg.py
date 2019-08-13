import os
import glob
import tempfile
import urllib.request
from pdf2image import convert_from_path
from pyunpack import Archive
from tqdm import tqdm, trange
#Tqmd
#Autodownload poppler
poppler = "poppler-0.67.0"
poppler_url = "https://blog.alivate.com.au/wp-content/uploads/2018/08/poppler-0.67.0_x86.7z"

with tempfile.TemporaryDirectory() as path:
    if not os.path.exists(poppler) or not os.listdir(poppler):
        #Download poppler
        class TqdmUpTo(tqdm):
            def update_to(self, b=1, bsize=1, tsize=None):
                if tsize is not None:
                    self.total = tsize
                self.update(b * bsize - self.n)
        with TqdmUpTo(unit='B', unit_scale=True, miniters=1, desc="Downloading poppler-0.67.0") as t:
            opener = urllib.request.build_opener()
            opener.addheaders = [("User-agent", "Mozialla/5.0")]
            urllib.request.install_opener(opener)
            urllib.request.urlretrieve(poppler_url, os.path.basename(poppler_url), reporthook=t.update_to, data=None)
        Archive(os.path.basename(poppler_url)).extractall("")
        os.remove(os.path.basename(poppler_url))
        print()

    files = glob.glob("*.pdf");
    for i in trange(len(files), desc="Converting files", miniters=1):
        file = files[i];
        images_from_path = convert_from_path(file, output_folder=path,
                                            last_page=1, first_page=0,
                                            poppler_path=poppler + "/bin")
        save_dir = '../images'
        for page in images_from_path:
            savename = os.path.join(save_dir, "IMG" + str(i) + ".jpeg")
            page.save(savename, "JPEG")
