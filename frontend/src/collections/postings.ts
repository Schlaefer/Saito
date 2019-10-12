import { Collection } from 'backbone';
import { PostingModel } from 'modules/posting/models/PostingModel';

export default class extends Collection {
    public model = PostingModel;
}
