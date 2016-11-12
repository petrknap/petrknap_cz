FROM stilliard/pure-ftpd:hardened
RUN (echo ""; echo "") | pure-pw useradd root -u ftpuser -d /home/ftpusers/root
RUN pure-pw mkdb
